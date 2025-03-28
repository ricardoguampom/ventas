<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleBarcode;
use App\Models\Category;
use App\Models\Entry;
use App\Models\EntryDetail;
use Illuminate\Http\Request;
use App\Models\PriceHistory;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;
use Barryvdh\DomPDF\Facade\Pdf;

class EntryController extends Controller
{
    public function __construct()
    {
        $this->middleware('check_permission:ingresos.listar')->only('index');
        $this->middleware('check_permission:ingresos.crear')->only(['create', 'store']);
        $this->middleware('check_permission:ingresos.ver')->only('show');
        $this->middleware('check_permission:ingresos.editar')->only(['edit', 'update']);
        $this->middleware('check_permission:ingresos.eliminar')->only('destroy');
        $this->middleware('check_permission:ingresos.reporte')->only([
            'showReportForm',
            'exportEntries',
            'exportEntriesToPdf',
            'exportEntriesToCsv'
        ]);
    }

    public function index(Request $request)
    {
        $articleQuery = $request->input('article_query');
        $categoryId = $request->input('category_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $supplierName = $request->input('supplier_name');
    
        $entries = Entry::with('details.article')
            ->when($articleQuery, function ($query) use ($articleQuery) {
                $query->whereHas('details.article', function ($subQuery) use ($articleQuery) {
                    $subQuery->where('name', 'like', "%{$articleQuery}%");
                });
            })
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->whereHas('details.article.category', function ($subQuery) use ($categoryId) {
                    $subQuery->where('id', $categoryId);
                });
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            })
            ->when($startDate && !$endDate, function ($query) use ($startDate) {
                $query->where('date', '>=', $startDate);
            })
            ->when($endDate && !$startDate, function ($query) use ($endDate) {
                $query->where('date', '<=', $endDate);
            })
            ->when($supplierName, function ($query) use ($supplierName) {
                $query->where('supplier_name', 'like', "%{$supplierName}%");
            })
            ->orderBy('date', 'desc')
            ->paginate(10);    
        return view('entries.index', compact('entries'));
    }
    

    public function create()
    {
        $articles = Article::with('barcodes')->get();
        return view('entries.create', compact('articles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'supplier_name' => 'nullable|string|max:255',
            'details' => 'required|json',
        ]);

        $details = json_decode($request->details, true);

        try {
            DB::transaction(function () use ($request, $details) {
                $entry = Entry::create([
                    'supplier_name' => $request->supplier_name,
                    'date' => $request->date,
                    'total' => array_sum(array_column($details, 'subtotal')),
                ]);

                foreach ($details as $detail) {
                    $article = Article::find($detail['article_id']);
                    if (!$article) {
                        throw new \Exception("Artículo no encontrado.");
                    }

                    $article->increment('stock', $detail['quantity']);

                    if (!empty($detail['barcodes'])) {
                        foreach ($detail['barcodes'] as $barcode) {
                            ArticleBarcode::updateOrCreate(
                                ['barcode' => $barcode],
                                ['article_id' => $article->id]
                            );
                        }
                    }

                    PriceHistory::create([
                        'article_id' => $article->id,
                        'old_cost' => $article->cost,
                        'new_cost' => $detail['price'],
                        'old_wholesale_price' => $article->wholesale_price,
                        'new_wholesale_price' => $detail['wholesale_price'],
                        'old_store_price' => $article->store_price,
                        'new_store_price' => $detail['store_price'],
                        'old_invoice_price' => $article->invoice_price,
                        'new_invoice_price' => $detail['invoice_price'],
                    ]);

                    $article->update([
                        'cost' => $detail['price'],
                        'wholesale_price' => $detail['wholesale_price'],
                        'store_price' => $detail['store_price'],
                        'invoice_price' => $detail['invoice_price'],
                    ]);

                    EntryDetail::create([
                        'entry_id' => $entry->id,
                        'article_id' => $detail['article_id'],
                        'quantity' => $detail['quantity'],
                        'price' => $detail['price'],
                        'wholesale_price' => $detail['wholesale_price'] ?? 0,
                        'store_price' => $detail['store_price'] ?? 0,
                        'invoice_price' => $detail['invoice_price'] ?? 0,
                    ]);
                }
            });

            Alert::success('Éxito', '¡Entrada creada con éxito!');
            return redirect()->route('entries.index');
        } catch (\Exception $e) {
            Alert::error('Error', 'Error al registrar la entrada: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }
    
    /**
     * Registrar cambios en los precios.
     */
    private function registerPriceChange(Article $article, $priceType, $newPrice)
    {
        $oldPrice = $article->$priceType;
    
        if ($oldPrice != $newPrice) {
            PriceHistory::create([
                'article_id' => $article->id,
                'old_price' => $oldPrice,
                'new_price' => $newPrice,
                'price_type' => $priceType,
                'changed_at' => now(),
            ]);
        }
    }

    public function edit(Entry $entry)
    {
        // Cargar los detalles del ingreso con los artículos relacionados
        $entry->load('details.article');
    
        // Cargar todos los artículos disponibles, igual que en create
        $articles = Article::with('barcodes')->get();
        return view('entries.edit', compact('entry', 'articles'));
    }
    
    public function update(Request $request, Entry $entry)
    {
        try {
            DB::transaction(function () use ($request, $entry) {
                // ✅ Decode 'details' JSON string if necessary
                $details = is_string($request->details) ? json_decode($request->details, true) : $request->details;
    
                if (!is_array($details)) {
                    throw new \Exception("Invalid details format: Expected an array but received " . gettype($details));
                }
    
                // ✅ Validate request
                $request->merge(['details' => $details]);
                $request->validate([
                    'date' => 'required|date',
                    'supplier_name' => 'nullable|string|max:255',
                    'details' => 'required|array',
                    'details.*.article_id' => 'required|exists:articles,id',
                    'details.*.quantity' => 'required|integer|min:1',
                    'details.*.price' => 'required|numeric|min:0',
                    'details.*.wholesale_price' => 'required|numeric|min:0',
                    'details.*.store_price' => 'required|numeric|min:0',
                    'details.*.invoice_price' => 'required|numeric|min:0',
                ]);
    
                // ✅ Get existing entry details
                $existingDetails = $entry->details->keyBy('article_id');
    
                // ✅ Adjust stock for removed items
                foreach ($existingDetails as $articleId => $oldDetail) {
                    $newDetail = collect($details)->firstWhere('article_id', $articleId);
                    $article = Article::findOrFail($articleId);
    
                    if ($newDetail) {
                        // 🔹 Capture old prices **before** updating the article
                        $oldPrices = [
                            'cost' => $article->cost,
                            'wholesale_price' => $article->wholesale_price,
                            'store_price' => $article->store_price,
                            'invoice_price' => $article->invoice_price,
                        ];
    
                        // 🔹 Update existing details and stock adjustment
                        $stockDifference = $newDetail['quantity'] - $oldDetail->quantity;
                        $article->increment('stock', $stockDifference);
    
                        // 🔥 **Ensure Price History Records Old & New Prices**
                        if (
                            $article->cost != $newDetail['price'] ||
                            $article->wholesale_price != $newDetail['wholesale_price'] ||
                            $article->store_price != $newDetail['store_price'] ||
                            $article->invoice_price != $newDetail['invoice_price']
                        ) {
                            PriceHistory::create([
                                'article_id' => $article->id,
                                'old_cost' => $oldPrices['cost'], // ✅ Capturing old cost
                                'new_cost' => $newDetail['price'], // ✅ Capturing new cost
                                'old_wholesale_price' => $oldPrices['wholesale_price'],
                                'new_wholesale_price' => $newDetail['wholesale_price'],
                                'old_store_price' => $oldPrices['store_price'],
                                'new_store_price' => $newDetail['store_price'],
                                'old_invoice_price' => $oldPrices['invoice_price'],
                                'new_invoice_price' => $newDetail['invoice_price'],
                                'changed_at' => now(),
                            ]);
                        }
    
                        // 🔹 Update entry detail record
                        $oldDetail->update([
                            'quantity' => $newDetail['quantity'],
                            'price' => $newDetail['price'],
                            'wholesale_price' => $newDetail['wholesale_price'],
                            'store_price' => $newDetail['store_price'],
                            'invoice_price' => $newDetail['invoice_price'],
                        ]);
    
                        // 🔹 Ensure article price is updated
                        $article->update([
                            'cost' => $newDetail['price'],
                            'wholesale_price' => $newDetail['wholesale_price'],
                            'store_price' => $newDetail['store_price'],
                            'invoice_price' => $newDetail['invoice_price'],
                        ]);
                    } else {
                        // 🔹 Remove item if it's not in the new request
                        $article->decrement('stock', $oldDetail->quantity);
                        $oldDetail->delete();
                    }
                }
    
                // ✅ Process new items
                foreach ($details as $detail) {
                    if (!isset($existingDetails[$detail['article_id']])) {
                        $article = Article::findOrFail($detail['article_id']);
    
                        // 🔹 Capture old prices **before** updating the article
                        $oldPrices = [
                            'cost' => $article->cost,
                            'wholesale_price' => $article->wholesale_price,
                            'store_price' => $article->store_price,
                            'invoice_price' => $article->invoice_price,
                        ];
    
                        // 🔹 Create new entry detail
                        EntryDetail::create([
                            'entry_id' => $entry->id,
                            'article_id' => $detail['article_id'],
                            'quantity' => $detail['quantity'],
                            'price' => $detail['price'],
                            'wholesale_price' => $detail['wholesale_price'],
                            'store_price' => $detail['store_price'],
                            'invoice_price' => $detail['invoice_price'],
                        ]);
    
                        // 🔹 Update stock
                        $article->increment('stock', $detail['quantity']);
    
                        // 🔥 **Ensure Price History Records Old & New Prices**
                        PriceHistory::create([
                            'article_id' => $article->id,
                            'old_cost' => $oldPrices['cost'], // ✅ Corrected field name
                            'new_cost' => $detail['price'], // ✅ Corrected field name
                            'old_wholesale_price' => $oldPrices['wholesale_price'],
                            'new_wholesale_price' => $detail['wholesale_price'],
                            'old_store_price' => $oldPrices['store_price'],
                            'new_store_price' => $detail['store_price'],
                            'old_invoice_price' => $oldPrices['invoice_price'],
                            'new_invoice_price' => $detail['invoice_price'],
                            'changed_at' => now(),
                        ]);
    
                        // 🔹 Ensure new article gets correct prices
                        $article->update([
                            'cost' => $detail['price'],
                            'wholesale_price' => $detail['wholesale_price'],
                            'store_price' => $detail['store_price'],
                            'invoice_price' => $detail['invoice_price'],
                        ]);
                    }
                }
    
                // ✅ Update entry info
                $entry->update([
                    'date' => $request->date,
                    'supplier_name' => $request->supplier_name,
                    'total' => array_sum(array_map(fn($d) => $d['quantity'] * $d['price'], $details)),
                ]);
            });
    
            Alert::success('Éxito', '¡Entrada actualizada con éxito!');
            return redirect()->route('entries.index');
    
        } catch (\Exception $e) {
            Alert::error('Error', 'Error al actualizar la entrada: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }
    
    
    
    
    public function show(Entry $entry)
    {
        // Cargar detalles del ingreso junto con los artículos
        $entry->load('details.article');

        return view('entries.show', compact('entry'));
    }

    public function destroy(Entry $entry)
    {
        try {
            DB::transaction(function () use ($entry) {
                foreach ($entry->details as $detail) {
                    $article = $detail->article;
                    $article->decrement('stock', $detail->quantity);
                    $detail->delete();
                }
                $entry->delete();
            });

            Alert::success('Eliminado', '¡Entrada eliminada con éxito!');
            return redirect()->route('entries.index');
        } catch (\Exception $e) {
            Alert::error('Error', 'Error al eliminar la entrada: ' . $e->getMessage());
            return redirect()->route('entries.index');
        }
    }
    public function showReportForm()
    {
        return view('entries.report');
    }
    
    public function exportEntries(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $format = $request->input('format', 'pdf'); // Por defecto, PDF

        $entries = Entry::with('details.article')
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();

        if ($format === 'pdf') {
            return $this->exportEntriesToPdf($entries, $startDate, $endDate);
        } elseif ($format === 'csv') {
            return $this->exportEntriesToCsv($entries, $startDate, $endDate);
        }

        return redirect()->back()->with('error', 'Formato no válido.');
    }

    public function exportEntriesToPdf($entries, $startDate, $endDate)
    {
        $totalInvestment = $entries->sum('total');

        $pdf = Pdf::loadView('entries.entries_report_pdf', compact('entries', 'startDate', 'endDate', 'totalInvestment'));

        return $pdf->download("reporte_ingresos_{$startDate}_{$endDate}.pdf");
    }

    public function exportEntriesToCsv($entries, $startDate, $endDate)
    {
        $filename = "reporte_ingresos_{$startDate}_{$endDate}.csv";

        $csvHeader = [
            'Fecha de Ingreso', 'Proveedor', 'Artículo', 'Cantidad', 'Precio Unitario', 'Subtotal'
        ];

        $csvData = [];
        foreach ($entries as $entry) {
            foreach ($entry->details as $detail) {
                $csvData[] = [
                    $entry->date,
                    $entry->supplier_name ?? 'N/A',
                    $detail->article->name ?? 'Artículo desconocido',
                    $detail->quantity,
                    number_format($detail->price, 2, '.', ''),
                    number_format($detail->quantity * $detail->price, 2, '.', ''),
                ];
            }
        }

        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename={$filename}");

        // Encabezado BOM para caracteres especiales
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($handle, $csvHeader);

        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);
        exit;
    }
}
