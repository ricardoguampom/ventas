<?php

namespace App\Http\Controllers;
use App\Models\ArticleBarcode;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class ArticleController extends Controller
{
    public function __construct()
    {
        // middleware personalizado para permisos

        $this->middleware('check_permission:articulos.listar')->only('index');
        $this->middleware('check_permission:articulos.ver')->only('show');
        $this->middleware('check_permission:articulos.ver_inversion')->only('totalInvestment');
        $this->middleware('check_permission:articulos.reporte_exportar')->only(['exportCsv', 'exportPdf', 'exportAllCsv', 'exportAllPdf']);
        $this->middleware('check_permission:articulos.crear')->only(['create', 'store']);
        $this->middleware('check_permission:articulos.editar')->only(['edit', 'update']);
        $this->middleware('check_permission:articulos.eliminar')->only(['destroy']);
    }

    public function show(Article $article, Request $request)
    {
        $query = $article->priceHistories()->orderBy('created_at', 'desc');
    
        // Convert dates properly if they exist
        if ($request->start_date && $request->end_date) {
            $start_date = date('Y-m-d 00:00:00', strtotime($request->start_date)); // Set time to start of day
            $end_date = date('Y-m-d 23:59:59', strtotime($request->end_date)); // Set time to end of day
            $query->whereBetween('created_at', [$start_date, $end_date]);
        }
    
        if ($request->min_price) {
            $query->where('new_cost', '>=', $request->min_price);
        }
    
        $priceHistory = $query->paginate(10);
        $barcodes = $article->barcodes;
    
        return view('articles.show', compact('article', 'priceHistory', 'barcodes'));
    }

    public function index(Request $request)
    {
        $query = $request->input('query');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $status = $request->input('status'); // ✅ Nuevo filtro para el estado del artículo
    
        $articles = Article::with(['category', 'barcodes'])
            ->where(function ($queryBuilder) use ($query) {
                if (!empty($query)) {
                    $queryBuilder->where('name', 'like', "%{$query}%")
                        ->orWhere('model', 'like', "%{$query}%")
                        ->orWhereHas('category', function ($categoryQuery) use ($query) {
                            $categoryQuery->where('name', 'like', "%{$query}%");
                        })
                        ->orWhereHas('barcodes', function ($barcodeQuery) use ($query) {
                            $barcodeQuery->where('barcode', 'like', "%{$query}%");
                        });
                }
            });
    
        // **💡 Filtrar por estado (activo/inactivo)**
        if ($status === '0' || $status === '1') {
            $articles->where('status', $status);
        }
    
        // **💡 Filtrar por rango de fechas**
        if (!empty($startDate) && !empty($endDate)) {
            $startDate = min($startDate, $endDate);
            $endDate = max($startDate, $endDate);
            $articles->whereBetween('expiration_date', [$startDate, $endDate]);
        } elseif (!empty($startDate)) {
            $articles->where('expiration_date', '>=', $startDate);
        } elseif (!empty($endDate)) {
            $articles->where('expiration_date', '<=', $endDate);
        }
    
        $articles = $articles->paginate(5);
    
        return view('articles.index', compact('articles', 'query', 'startDate', 'endDate', 'status'));
    }
    
    
    public function create()
    {
        $categories = Category::where('status', 1)->get();
        return view('articles.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'model' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'expiration_date' => 'nullable|date',
            'status' => 'required|in:0,1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'barcodes' => 'nullable|array',
            'barcodes.*' => 'string|max:100|unique:article_barcodes,barcode',
            'cost' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'store_price' => 'nullable|numeric|min:0',
            'invoice_price' => 'nullable|numeric|min:0',
        ]);

        $data = $request->only([
            'category_id', 'name', 'model', 'description', 'expiration_date', 'status'
        ]);

        $data['cost'] = $request->input('cost', 0);
        $data['wholesale_price'] = $request->input('wholesale_price', 0);
        $data['store_price'] = $request->input('store_price', 0);
        $data['invoice_price'] = $request->input('invoice_price', 0);
        $data['stock'] = 0;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('articles', 'public');
        }

        try {
            DB::transaction(function () use ($data, $request) {
                $article = Article::create($data);

                if ($request->has('barcodes')) {
                    foreach ($request->barcodes as $barcode) {
                        ArticleBarcode::create(['article_id' => $article->id, 'barcode' => $barcode]);
                    }
                }
            });

            Alert::success('Éxito', '¡Artículo creado con éxito!');
            return redirect()->route('articles.index');
        } catch (\Exception $e) {
            Alert::error('Error', 'Error al guardar el artículo: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }
    
    public function edit(Article $article)
    {
        $categories = Category::where('status', 1)->get();
        return view('articles.edit', compact('article', 'categories'));
    }

    public function update(Request $request, Article $article)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'model' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'expiration_date' => 'nullable|date',
            'status' => 'required|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'barcodes' => 'nullable|array',
            'barcodes.*' => 'string|max:100|unique:article_barcodes,barcode,' . $article->id . ',article_id',
            'cost' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'store_price' => 'nullable|numeric|min:0',
            'invoice_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $data = $request->only([
            'category_id', 'name', 'model', 'description', 'expiration_date', 'status',
            'cost', 'wholesale_price', 'store_price', 'invoice_price', 'stock'
        ]);

        if ($request->hasFile('image')) {
            if ($article->image) {
                Storage::disk('public')->delete($article->image);
            }
            $data['image'] = $request->file('image')->store('articles', 'public');
        }

        try {
            DB::transaction(function () use ($article, $data, $request) {
                $article->update($data);

                if ($request->has('barcodes')) {
                    ArticleBarcode::where('article_id', $article->id)->delete();
                    foreach ($request->barcodes as $barcode) {
                        ArticleBarcode::create(['article_id' => $article->id, 'barcode' => $barcode]);
                    }
                }
            });

            Alert::success('Éxito', '¡Artículo actualizado con éxito!');
            return redirect()->route('articles.index');
        } catch (\Exception $e) {
            Alert::error('Error', 'Error al actualizar el artículo: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function destroy(Article $article)
    {
        try {
            DB::transaction(function () use ($article) {
                if ($article->image) {
                    Storage::disk('public')->delete($article->image);
                }
                $article->barcodes()->delete();
                $article->delete();
            });
    
            return response()->json(['success' => 'Artículo eliminado con éxito.']);
    
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar el artículo: ' . $e->getMessage()], 500);
        }
    }    
    
    public function exportCsv(Article $article, Request $request)
    {
        $query = $article->priceHistories()->orderBy('created_at', 'desc');
    
        if ($request->start_date && $request->end_date) {
            $startDate = date('Y-m-d H:i:s', strtotime($request->start_date));
            $endDate = date('Y-m-d H:i:s', strtotime($request->end_date));
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
    
        if ($request->min_price) {
            $query->where('new_cost', '>=', $request->min_price);
        }
    
        $priceHistory = $query->get();
    
        $csvHeader = ['Precio de Compra', 'Precio al Por Mayor', 'Precio en Tienda', 'Precio en Factura', 'Fecha'];
        $csvData = $priceHistory->map(function ($history) {
            return [
                number_format($history->new_cost, 2, '.', ''), // Formato de número compatible
                number_format($history->new_wholesale_price, 2, '.', ''),
                number_format($history->new_store_price, 2, '.', ''),
                number_format($history->new_invoice_price, 2, '.', ''),
                $history->created_at->format('Y-m-d H:i:s'),
            ];
        });
    
        $filename = 'historial_precios_' . $article->id . '.csv';
    
        // Utilizando un buffer de salida para crear el contenido CSV
        $output = fopen('php://output', 'w');
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
    
        // Agregar el encabezado de BOM para UTF-8 (permite caracteres especiales en Excel)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
        // Escribir encabezados
        fputcsv($output, $csvHeader);
    
        // Escribir datos
        foreach ($csvData as $row) {
            fputcsv($output, $row);
        }
    
        fclose($output);
        exit;
    }
    
    
    public function exportPdf(Article $article, Request $request)
    {
        // Consulta base para el historial de precios
        $query = $article->priceHistories()->orderBy('created_at', 'desc');
    
        // Aplicar filtros con hora
        if ($request->start_date && $request->end_date) {
            $startDate = date('Y-m-d H:i:s', strtotime($request->start_date));
            $endDate = date('Y-m-d H:i:s', strtotime($request->end_date));
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
    
        // Filtrar por precio mínimo usando `new_cost` en lugar de `price`
        if ($request->min_price) {
            $query->where('new_cost', '>=', $request->min_price);
        }
    
        // Obtener los datos filtrados (sin paginación para PDF)
        $priceHistory = $query->get();
    
        // Generar el PDF usando la vista
        $pdf = Pdf::loadView('articles.price_history_pdf', [
            'article' => $article,
            'priceHistory' => $priceHistory,
        ]);
    
        return $pdf->download('price_history_' . $article->id . '.pdf');
    }
    
    public function exportAllCsv()
    {
        $articles = Article::with('category')->get();
    
        $csvHeader = [
            'ID', 
            'Categoría', 
            'Código', 
            'Nombre', 
            'Stock', 
            'Costo', 
            'Precio al por mayor', 
            'Precio de tienda', 
            'Precio de factura', 
            'Fecha de caducidad', 
            'Estado', 
            'Creado en'
        ];
    
        $csvData = $articles->map(function ($article) {
            return [
                $article->id,
                $article->category->name ?? 'N/A',
                $article->code,
                $article->name,
                $article->stock,
                number_format($article->cost, 2, '.', ''), // Asegurar el formato numérico
                number_format($article->wholesale_price, 2, '.', ''),
                number_format($article->store_price, 2, '.', ''),
                number_format($article->invoice_price, 2, '.', ''),
                $article->expiration_date ?? 'N/A',
                $article->status ? 'Activo' : 'Inactivo',
                $article->created_at->format('Y-m-d H:i:s'),
            ];
        });
    
        $filename = 'all_articles.csv';
    
        // Utilizando un buffer de salida para crear el contenido CSV
        $output = fopen('php://output', 'w');
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
    
        // Agregar el encabezado de BOM para UTF-8 (permite caracteres especiales en Excel)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
        // Escribir encabezados
        fputcsv($output, $csvHeader);
    
        // Escribir datos
        foreach ($csvData as $row) {
            fputcsv($output, $row);
        }
    
        fclose($output);
        exit;
    }
    

    public function exportAllPdf()
    {
        $articles = Article::with('category')->get();
    
        $pdf = Pdf::loadView('articles.all_articles_pdf', compact('articles'));
    
        return $pdf->download('all_articles.pdf');
    }
    //totalInvestment FIFO
    public function totalInvestment()
    {        
        $articles = Article::with(['entryDetails' => function ($query) {
            $query->orderBy('created_at', 'desc'); // Ordenamos los ingresos del más reciente al más antiguo
        }])->get();
    
        $totalCapital = 0;
    
        foreach ($articles as $article) {
            $stockRemaining = $article->stock; // Stock actual del artículo
            $articleCapital = 0;
    
            foreach ($article->entryDetails as $entry) {
                if ($stockRemaining <= 0) {
                    break; // Si ya hemos valorado todo el stock, salimos del bucle
                }
    
                // Tomamos la cantidad disponible en este ingreso
                $usedQuantity = min($entry->quantity, $stockRemaining);
    
                // Sumamos el valor de estas unidades al total del capital
                $articleCapital += $usedQuantity * $entry->price;
    
                // Reducimos el stock restante
                $stockRemaining -= $usedQuantity;
            }
    
            // Sumamos al capital total
            $totalCapital += $articleCapital;
        }
    
        return response()->json([
            'total_investment' => number_format($totalCapital, 2, '.', ',')
        ]);
    }

}
