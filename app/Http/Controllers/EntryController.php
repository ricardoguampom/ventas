<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleBarcode;
use App\Models\Entry;
use App\Models\EntryDetail;
use App\Models\Person;
use App\Models\PriceHistory;
use Illuminate\Http\Request;
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
            'showReportForm', 'exportEntries', 'exportEntriesToPdf', 'exportEntriesToCsv'
        ]);
    }

    public function index(Request $request)
    {
        $entries = Entry::with(['details.article', 'provider'])
            ->when($request->article_query, fn($q) => $q->whereHas('details.article', fn($s) => $s->where('name', 'like', "%{$request->article_query}%")))
            ->when($request->category_id, fn($q) => $q->whereHas('details.article.category', fn($s) => $s->where('id', $request->category_id)))
            ->when($request->start_date && $request->end_date, fn($q) => $q->whereBetween('date', [$request->start_date, $request->end_date]))
            ->when($request->start_date && !$request->end_date, fn($q) => $q->where('date', '>=', $request->start_date))
            ->when($request->end_date && !$request->start_date, fn($q) => $q->where('date', '<=', $request->end_date))
            ->when($request->supplier_name, fn($q) => $q->whereHas('provider', fn($s) => $s->where('name', 'like', "%{$request->supplier_name}%")))
            ->orderBy('date', 'desc')
            ->paginate(5);
        return view('entries.index', compact('entries'));
    }

    public function create()
    {
        $articles = Article::with('barcodes')->get();
        $providers = Person::where('type', 'provider')->get();
        return view('entries.create', compact('articles', 'providers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'provider_id' => 'required|exists:people,id',
            'details' => 'required|json',
        ]);

        $details = json_decode($request->details, true);

        try {
            DB::transaction(function () use ($request, $details) {
                $entry = Entry::create([
                    'provider_id' => $request->provider_id,
                    'date' => $request->date,
                    'total' => array_sum(array_column($details, 'subtotal')),
                ]);

                foreach ($details as $detail) {
                    $article = Article::findOrFail($detail['article_id']);
                    $article->increment('stock', $detail['quantity']);

                    if (!empty($detail['barcodes'])) {
                        foreach ($detail['barcodes'] as $barcode) {
                            ArticleBarcode::updateOrCreate(
                                ['barcode' => $barcode],
                                ['article_id' => $article->id]
                            );
                        }
                    }

                    EntryDetail::create([
                        'entry_id' => $entry->id,
                        'article_id' => $article->id,
                        'quantity' => $detail['quantity'],
                        'price' => $detail['price'],
                        'wholesale_price' => $detail['wholesale_price'] ?? 0,
                        'store_price' => $detail['store_price'] ?? 0,
                        'invoice_price' => $detail['invoice_price'] ?? 0,
                    ]);

                    PriceHistory::create([
                        'entry_id' => $entry->id,
                        'article_id' => $article->id,
                        'old_cost' => $article->cost,
                        'new_cost' => $detail['price'],
                        'old_wholesale_price' => $article->wholesale_price,
                        'new_wholesale_price' => $detail['wholesale_price'],
                        'old_store_price' => $article->store_price,
                        'new_store_price' => $detail['store_price'],
                        'old_invoice_price' => $article->invoice_price,
                        'new_invoice_price' => $detail['invoice_price'],
                        'changed_at' => now(),
                    ]);

                    $article->update([
                        'cost' => $detail['price'],
                        'wholesale_price' => $detail['wholesale_price'],
                        'store_price' => $detail['store_price'],
                        'invoice_price' => $detail['invoice_price'],
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

    public function edit(Entry $entry)
    {
        $entry->load('details.article');
        $articles = Article::with('barcodes')->get();
        $providers = Person::where('type', 'provider')->get();
        return view('entries.edit', compact('entry', 'articles', 'providers'));
    }

    public function update(Request $request, Entry $entry)
    {
        try {
            DB::transaction(function () use ($request, $entry) {
                $details = is_string($request->details) ? json_decode($request->details, true) : $request->details;
                if (!is_array($details)) throw new \Exception("Invalid details format");

                $existingDetails = $entry->details->keyBy('article_id');

                foreach ($existingDetails as $articleId => $oldDetail) {
                    $newDetail = collect($details)->firstWhere('article_id', $articleId);
                    $article = Article::findOrFail($articleId);

                    if ($newDetail) {
                        $stockDifference = $newDetail['quantity'] - $oldDetail->quantity;
                        $article->increment('stock', $stockDifference);

                        $oldDetail->update([
                            'quantity' => $newDetail['quantity'],
                            'price' => $newDetail['price'],
                            'wholesale_price' => $newDetail['wholesale_price'],
                            'store_price' => $newDetail['store_price'],
                            'invoice_price' => $newDetail['invoice_price'],
                        ]);

                        PriceHistory::updateOrCreate(
                            ['entry_id' => $entry->id, 'article_id' => $article->id],
                            [
                                'old_cost' => $article->cost,
                                'new_cost' => $newDetail['price'],
                                'old_wholesale_price' => $article->wholesale_price,
                                'new_wholesale_price' => $newDetail['wholesale_price'],
                                'old_store_price' => $article->store_price,
                                'new_store_price' => $newDetail['store_price'],
                                'old_invoice_price' => $article->invoice_price,
                                'new_invoice_price' => $newDetail['invoice_price'],
                                'changed_at' => now(),
                            ]
                        );

                        $article->update([
                            'cost' => $newDetail['price'],
                            'wholesale_price' => $newDetail['wholesale_price'],
                            'store_price' => $newDetail['store_price'],
                            'invoice_price' => $newDetail['invoice_price'],
                        ]);
                    } else {
                        $article->decrement('stock', $oldDetail->quantity);
                        $oldDetail->delete();
                        PriceHistory::where(['entry_id' => $entry->id, 'article_id' => $article->id])->delete();
                    }
                }

                foreach ($details as $detail) {
                    if (!isset($existingDetails[$detail['article_id']])) {
                        $article = Article::findOrFail($detail['article_id']);

                        EntryDetail::create([
                            'entry_id' => $entry->id,
                            'article_id' => $detail['article_id'],
                            'quantity' => $detail['quantity'],
                            'price' => $detail['price'],
                            'wholesale_price' => $detail['wholesale_price'],
                            'store_price' => $detail['store_price'],
                            'invoice_price' => $detail['invoice_price'],
                        ]);

                        $article->increment('stock', $detail['quantity']);

                        PriceHistory::create([
                            'entry_id' => $entry->id,
                            'article_id' => $article->id,
                            'old_cost' => $article->cost,
                            'new_cost' => $detail['price'],
                            'old_wholesale_price' => $article->wholesale_price,
                            'new_wholesale_price' => $detail['wholesale_price'],
                            'old_store_price' => $article->store_price,
                            'new_store_price' => $detail['store_price'],
                            'old_invoice_price' => $article->invoice_price,
                            'new_invoice_price' => $detail['invoice_price'],
                            'changed_at' => now(),
                        ]);

                        $article->update([
                            'cost' => $detail['price'],
                            'wholesale_price' => $detail['wholesale_price'],
                            'store_price' => $detail['store_price'],
                            'invoice_price' => $detail['invoice_price'],
                        ]);
                    }
                }

                $entry->update([
                    'date' => $request->date,
                    'provider_id' => $request->provider_id,
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
        $entry->load(['details.article', 'provider']);
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
                    PriceHistory::where(['entry_id' => $entry->id, 'article_id' => $article->id])->delete();
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
        $format = $request->input('format', 'pdf');

        $entries = Entry::with('details.article')
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();

        return $format === 'pdf'
            ? $this->exportEntriesToPdf($entries, $startDate, $endDate)
            : $this->exportEntriesToCsv($entries, $startDate, $endDate);
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
        $csvHeader = ['Ingreso #', 'Fecha de Ingreso', 'Proveedor', 'Artículo', 'Cantidad', 'Precio Unitario (Bs)', 'Subtotal (Bs)'];
        $csvData = [];
    
        foreach ($entries as $entry) {
            $entryTotal = 0;
    
            foreach ($entry->details as $detail) {
                $subtotal = $detail->quantity * $detail->price;
                $entryTotal += $subtotal;
    
                $csvData[] = [
                    $entry->id,
                    $entry->date,
                    optional($entry->provider)->name ?? 'Sin proveedor',
                    $detail->article->name ?? 'Artículo desconocido',
                    $detail->quantity,
                    number_format($detail->price, 2, '.', ''),
                    number_format($subtotal, 2, '.', ''),
                ];
            }
    
            // Fila de total por ingreso
            $csvData[] = [
                '', '', '', 'TOTAL DEL INGRESO', '', '', number_format($entryTotal, 2, '.', '')
            ];
        }
    
        // Generación del CSV
        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename={$filename}");
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
    
        fputcsv($handle, $csvHeader);
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
        exit;
    }
    
}
