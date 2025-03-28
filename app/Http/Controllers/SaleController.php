<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;
use RealRashid\SweetAlert\Facades\Alert;

class SaleController extends Controller
{
    public function __construct()
    {
        $this->middleware('check_permission:ventas.listar')->only('index');
        $this->middleware('check_permission:ventas.ver')->only('show');
        $this->middleware('check_permission:ventas.crear')->only(['create', 'store']);
        $this->middleware('check_permission:ventas.editar')->only(['edit', 'update']);
        $this->middleware('check_permission:ventas.eliminar')->only('destroy');
        $this->middleware('check_permission:ventas.reporte')->only([
            'reports', 
            'exportSalesReport', 
            'exportPdf',
            'exportQuotations', 
            'exportPendingSales'
        ]);
    }

    public function index(Request $request)
    {
        $sales = Sale::with('details.article')
            ->when($request->customer_name, fn($q) => $q->where('customer_name', 'like', "%{$request->customer_name}%"))
            ->when($request->invoice_number, fn($q) => $q->where('invoice_number', 'like', "%{$request->invoice_number}%"))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->start_date && $request->end_date, function ($q) use ($request) {
                $q->whereBetween('created_at', [
                    date('Y-m-d 00:00:00', strtotime($request->start_date)),
                    date('Y-m-d 23:59:59', strtotime($request->end_date))
                ]);
            })
            ->paginate(10);

        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $articles = Article::with('barcodes')->get(); // 🔥 Cargar TODOS los artículos
        $invoiceNumber = Sale::generateInvoiceNumber();
        return view('sales.create', compact('articles', 'invoiceNumber'));
    }
    

    public function store(Request $request)
    {
        try {
            $details = json_decode($request->input('details'), true);
    
            if (empty($details) || !is_array($details)) {
                return redirect()->back()->with('error', 'La venta debe incluir al menos un artículo válido.')->withInput();
            }
    
            $request->validate([
                'customer_name' => 'required|string|max:255',
                'invoice_number' => 'required|string|unique:sales,invoice_number|max:50',
                'total' => 'required|numeric|min:0',
            ], [
                'invoice_number.unique' => 'El número de factura ya está registrado.', // 📌 Mensaje en español
            ]);
            
    
            DB::transaction(function () use ($request, $details) {
                $sale = Sale::create([
                    'user_id' => Auth::id(),
                    'customer_name' => $request->customer_name,
                    'invoice_number' => $request->invoice_number,
                    'total' => $request->total,
                    'status' => 'pending',
                    'is_quotation' => true,
                ]);
    
                foreach ($details as $detail) {
                    $article = Article::findOrFail($detail['article_id']);
    
                    SaleDetail::create([
                        'sale_id' => $sale->id,
                        'article_id' => $detail['article_id'],
                        'quantity' => $detail['quantity'],
                        'price' => $detail['price'],
                    ]);
                }
            });
    
            return redirect()->route('sales.index')->with('success', '¡Venta creada con éxito!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // 🔹 Si la validación falla, capturamos los errores y los mostramos
            return redirect()->back()->withErrors($e->validator->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al crear la venta: ' . $e->getMessage())->withInput();
        }
    }
    

    public function edit(Sale $sale)
    {
        $sale->load('details.article.barcodes'); // Cargar detalles con artículos y códigos de barras
        $articles = Article::with('barcodes')->where('status', 1)->get();
    
        // Formatear detalles para la tabla dinámica
        $saleDetails = $sale->details->map(function ($detail) {
            return [
                'article_id' => $detail->article_id,
                'name' => $detail->article->name,
                'quantity' => $detail->quantity,
                'price' => (float) $detail->price,  // ✅ Convertir a float
                'subtotal' => (float) $detail->quantity * (float) $detail->price,  // ✅ Convertir a float
                'prices' => [
                    'storePrice' => (float) $detail->article->store_price,  // ✅ Convertir a float
                    'wholesalePrice' => (float) $detail->article->wholesale_price,  // ✅ Convertir a float
                    'invoicePrice' => (float) $detail->article->invoice_price,  // ✅ Convertir a float
                ],
            ];
        });
    
        return view('sales.edit', compact('sale', 'articles', 'saleDetails'));
    }
    
    public function update(Request $request, Sale $sale)
    {
        try {
            // ✅ Validación del formulario
            $validatedData = $request->validate([
                'customer_name' => 'required|string|max:255',
                'invoice_number' => 'required|string|max:255',
                'total' => 'required|numeric|min:0',
                'details' => 'required|string', // Se decodifica manualmente
            ]);
    
            // ✅ Decodificar JSON de los detalles
            $details = json_decode($validatedData['details'], true);
    
            if (!is_array($details) || count($details) === 0) {
                throw new \Exception('Los detalles de la venta no son válidos.');
            }
    
            // ✅ Validar cada detalle de la venta
            foreach ($details as $detail) {
                if (
                    !isset($detail['article_id'], $detail['quantity'], $detail['price']) ||
                    !is_numeric($detail['article_id']) ||
                    !is_numeric($detail['quantity']) ||
                    !is_numeric($detail['price'])
                ) {
                    throw new \Exception('Uno o más detalles de la venta son inválidos.');
                }
            }
    
            DB::transaction(function () use ($sale, $validatedData, $details) {
                // ✅ Actualizar información de la venta (independiente de cotización o venta)
                $sale->update([
                    'customer_name' => $validatedData['customer_name'],
                    'invoice_number' => $validatedData['invoice_number'],
                    'total' => $validatedData['total'],
                ]);
    
                // ✅ Obtener detalles existentes de la venta
                $existingDetails = $sale->details->keyBy('article_id');
    
                // ✅ Procesar nuevos detalles
                foreach ($details as $detail) {
                    $article = Article::findOrFail($detail['article_id']);
    
                    if (isset($existingDetails[$detail['article_id']])) {
                        // 🔹 Si el producto ya existía en la venta, actualizarlo
                        $existingSaleDetail = $existingDetails[$detail['article_id']];
                        
                        // ✅ Si es una COTIZACIÓN, **NO modificar stock**
                        if (!$sale->is_quotation) {
                            $previousQuantity = $existingSaleDetail->quantity;
                            $newQuantity = $detail['quantity'];
    
                            if ($newQuantity > $previousQuantity) {
                                // 🔥 Si la cantidad aumentó, reducir stock
                                $stockDifference = $newQuantity - $previousQuantity;
                                $article->decrement('stock', $stockDifference);
                            } elseif ($newQuantity < $previousQuantity) {
                                // 🔥 Si la cantidad disminuyó, devolver stock
                                $stockDifference = $previousQuantity - $newQuantity;
                                $article->increment('stock', $stockDifference);
                            }
                        }
    
                        // ✅ Actualizar el detalle de la venta
                        $existingSaleDetail->update([
                            'quantity' => $detail['quantity'],
                            'price' => $detail['price'],
                        ]);
                    } else {
                        // 🔹 Si el producto es nuevo, insertarlo
                        SaleDetail::create([
                            'sale_id' => $sale->id,
                            'article_id' => $detail['article_id'],
                            'quantity' => $detail['quantity'],
                            'price' => $detail['price'],
                        ]);
    
                        // ✅ **Solo modificar stock si NO es una cotización**
                        if (!$sale->is_quotation) {
                            $article->decrement('stock', $detail['quantity']);
                        }
                    }
                }
    
                // ✅ Eliminar productos eliminados de la venta
                foreach ($existingDetails as $articleId => $oldDetail) {
                    if (!collect($details)->pluck('article_id')->contains($articleId)) {
                        // ✅ Si la venta NO es una cotización, devolver stock antes de eliminar
                        if (!$sale->is_quotation) {
                            $article = Article::findOrFail($articleId);
                            $article->increment('stock', $oldDetail->quantity);
                        }
    
                        $oldDetail->delete();
                    }
                }
            });
    
            Alert::success('Éxito', '¡Venta actualizada con éxito!');
            return redirect()->route('sales.index');
    
        } catch (\Exception $e) {
            Alert::error('Error', $e->getMessage());
            return back()->withInput();
        }
    }
    
    /**
     * Muestra los detalles de la venta.
     */
    public function show(Sale $sale)
    {
        $sale->load('details.article');
        return view('sales.show', compact('sale'));
    }

    /**
     * Convierte una cotización en venta.
     */
    public function convertToSale(Sale $sale)
    {
        try {
            if ($sale->is_quotation) {
                foreach ($sale->details as $detail) {
                    $article = $detail->article;
                    if ($article->stock < $detail->quantity) {
                        return response()->json([
                            'error' => "Stock insuficiente para el artículo \"{$article->name}\". Disponible: {$article->stock}, solicitado: {$detail->quantity}."
                        ], 400);
                    }
                }
    
                $sale->convertToSale(); // Método para convertir la cotización a venta.
    
                return response()->json([
                    'success' => '¡Cotización convertida a venta con éxito!'
                ]);
            }
    
            return response()->json([
                'error' => 'Esta transacción no es una cotización.'
            ], 400);
        } catch (\Exception $e) {
            // **🔥 Registra el error en el log de Laravel**
            \Log::error("Error al convertir cotización a venta: " . $e->getMessage());
    
            return response()->json([
                'error' => 'Error inesperado: ' . $e->getMessage()
            ], 500);
        }
    }
    

    /**
     * Actualiza el estado de una venta (AJAX Request).
     */
    public function updateStatus(Request $request, Sale $sale)
    {
        $request->validate(['status' => 'required|in:pending,paid,cancelled']);

        if ($sale->status === 'cancelled') {
            return response()->json(['error' => 'No se puede actualizar una venta cancelada.'], 403);
        }

        DB::transaction(function () use ($sale, $request) {
            if ($request->status === 'cancelled') {
                foreach ($sale->details as $detail) {
                    $detail->article->increment('stock', $detail->quantity);
                }
            }
            $sale->update(['status' => $request->status]);
        });

        return response()->json(['success' => true, 'message' => '¡Estado de la venta actualizado con éxito!']);
    }

    /**
     * Cancela una venta y devuelve stock.
     */
    public function cancelSale(Sale $sale)
    {
        if ($sale->status === 'cancelled') {
            return response()->json(['error' => 'La venta ya está cancelada.'], 403);
        }

        DB::transaction(function () use ($sale) {
            foreach ($sale->details as $detail) {
                $detail->article->increment('stock', $detail->quantity);
            }
            $sale->update(['status' => 'cancelled']);
        });

        return response()->json(['success' => true, 'message' => '¡Venta cancelada y stock restaurado con éxito!']);
    }

    /**
     * Elimina una venta (Solo si está pendiente).
     */
    public function destroy(Sale $sale)
    {
        if ($sale->status !== 'pending') {
            Alert::error('Error', 'Solo se pueden eliminar ventas pendientes.');
            return redirect()->route('sales.index');
        }

        try {
            DB::transaction(function () use ($sale) {
                if (!$sale->is_quotation) {
                    foreach ($sale->details as $detail) {
                        $detail->article->increment('stock', $detail->quantity);
                    }
                }

                $sale->details()->delete();
                $sale->delete();
            });

            Alert::success('Eliminado', '¡Venta eliminada con éxito!');
            return redirect()->route('sales.index');
        } catch (\Exception $e) {
            Alert::error('Error', $e->getMessage());
            return redirect()->route('sales.index');
        }
    }
    
    /**
     * Exporta la factura en PDF.
     */
    public function exportPdf(Sale $sale)
    {
        $sale->load('details.article');
        $pdf = Pdf::loadView('sales.invoice_pdf', compact('sale'));
        return $pdf->download('invoice_' . $sale->invoice_number . '.pdf');
    }
    public function reports()
    {
        return view('sales.reports'); // Nueva vista para reportes
    }
    public function exportSalesReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'required|in:pdf,csv'
        ]);
    
        $startDate = $request->start_date;
        $endDate = $request->end_date;
    
        // 📌 Obtener ventas "paid" con detalles y usuario
        $sales = \App\Models\Sale::with(['details.article', 'user'])
            ->where('status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'asc')
            ->get();
    
        // 📌 Calcular total de ventas pagadas y cantidad
        $totalSalesAmount = $sales->sum('total');
        $totalSalesCount = $sales->count();
    
        if ($request->format === 'pdf') {
            // 📄 Generar PDF con desglose y usuario
            $pdf = Pdf::loadView('sales.report_pdf', compact('sales', 'startDate', 'endDate', 'totalSalesAmount', 'totalSalesCount'));
            return $pdf->download("reporte_ventas_{$startDate}_{$endDate}.pdf");
        } else {
            // 📊 Generar CSV con detalles y usuario
            $csvFileName = "reporte_ventas_{$startDate}_{$endDate}.csv";
            $csvHeader = ['#', 'Fecha', 'Cliente', 'Factura', 'Usuario', 'Artículo', 'Cantidad', 'Precio Unitario', 'Subtotal', 'Total Venta'];
    
            $csvData = [];
            $counter = 1;
            foreach ($sales as $sale) {
                foreach ($sale->details as $detail) {
                    $csvData[] = [
                        $counter++, // ✅ ID de conteo en lugar del ID de venta
                        $sale->created_at,
                        $sale->customer_name,
                        $sale->invoice_number,
                        $sale->user->name, // ✅ Nombre del usuario que realizó la venta
                        $detail->article->name,
                        $detail->quantity,
                        number_format($detail->price, 2, '.', ','),
                        number_format($detail->quantity * $detail->price, 2, '.', ','),
                        number_format($sale->total, 2, '.', ','),
                    ];
                }
            }
    
            // 📌 Crear CSV
            $handle = fopen('php://output', 'w');
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $csvFileName . '"');
    
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM para compatibilidad con Excel
            fputcsv($handle, $csvHeader);
            foreach ($csvData as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
            exit;
        }
    }
    public function exportQuotations(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $format = $request->input('format');
    
        $quotations = Sale::where('is_quotation', true)
            ->where('status', 'pending') 
            ->when($startDate, fn($query, $startDate) => $query->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($query, $endDate) => $query->whereDate('created_at', '<=', $endDate))
            ->with(['user', 'details.article'])
            ->get();
    
        if ($format === 'pdf') {
            $pdf = Pdf::loadView('sales.quotations_pdf', compact('quotations', 'startDate', 'endDate'));
            return $pdf->download('reporte_cotizaciones.pdf');
        } elseif ($format === 'csv') {
            return $this->exportQuotationsCsv($quotations);
        }
    }

    public function exportPendingSales(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $format = $request->input('format');

        $pendingSales = Sale::where('is_quotation', false)
            ->where('status', 'pending')
            ->when($startDate, fn($query, $startDate) => $query->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($query, $endDate) => $query->whereDate('created_at', '<=', $endDate))
            ->with(['user', 'details.article'])
            ->get();

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('sales.pending_sales_pdf', compact('pendingSales', 'startDate', 'endDate'));
            return $pdf->download('reporte_ventas_pendientes.pdf');
        } elseif ($format === 'csv') {
            return $this->exportPendingSalesCsv($pendingSales);
        }
    }

    private function exportPendingSalesCsv($pendingSales)
    {
        $csvHeader = ['Fecha', 'Usuario', 'Cliente', 'Artículo', 'Cantidad', 'Precio Unitario (Bs)', 'Subtotal (Bs)', 'Total Venta (Bs)'];
        $csvData = [];

        foreach ($pendingSales as $sale) {
            foreach ($sale->details as $detail) {
                $csvData[] = [
                    $sale->created_at->format('Y-m-d H:i:s'),
                    $sale->user->name ?? 'Desconocido',
                    $sale->customer_name,
                    $detail->article->name ?? 'Artículo eliminado',
                    $detail->quantity,
                    number_format($detail->price, 2, '.', ''),
                    number_format($detail->quantity * $detail->price, 2, '.', ''),
                    number_format($sale->total, 2, '.', ''),
                ];
            }
        }

        $filename = 'reporte_ventas_pendientes.csv';
        $output = fopen('php://output', 'w');
        header('Content-Type: text/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename=\"$filename\"");

        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, $csvHeader);
        foreach ($csvData as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }
    
    /**
     * Exportar cotizaciones en formato CSV con detalles.
     */
    private function exportQuotationsCsv($quotations)
    {
        $csvHeader = ['Fecha', 'Usuario', 'Cliente', 'Artículo', 'Cantidad', 'Precio Unitario (Bs)', 'Subtotal (Bs)', 'Total Cotización (Bs)'];
        $csvData = [];
    
        foreach ($quotations as $q) {
            foreach ($q->details as $detail) {
                $csvData[] = [
                    $q->created_at->format('Y-m-d H:i:s'),
                    $q->user->name ?? 'Desconocido',
                    $q->customer_name,
                    $detail->article->name ?? 'Artículo eliminado',
                    $detail->quantity,
                    number_format($detail->price, 2, '.', ''),
                    number_format($detail->quantity * $detail->price, 2, '.', ''),
                    number_format($q->total, 2, '.', ''),
                ];
            }
        }
    
        $filename = 'reporte_cotizaciones.csv';
        $output = fopen('php://output', 'w');
        header('Content-Type: text/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename=\"$filename\"");
    
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM para UTF-8
        fputcsv($output, $csvHeader);
        foreach ($csvData as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }
    
}
