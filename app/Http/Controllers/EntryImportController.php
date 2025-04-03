<?php

namespace App\Http\Controllers;

use App\Models\{Person, Category, Article, ArticleBarcode, Entry, EntryDetail, PriceHistory};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class EntryImportController extends Controller
{
    public function __construct()
    {
        $this->middleware('check_permission:ingresos.importar_ingresos')->only([
            'importForm', 
            'template', 
            'preview', 
            'confirm'
        ]);
    }

    public function importForm()
    {
        return view('entries.import');
    }

    public function template()
    {
        $csv = fopen('php://output', 'w');
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="plantilla_ingreso.csv"');
        fprintf($csv, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($csv, [
            'nombre_proveedor', 'nombre_categoria', 'nombre_articulo', 'modelo', 'descripcion',
            'cantidad', 'costo', 'precio_mayor', 'precio_tienda', 'precio_factura', 'fecha_vencimiento', 'codigo_barra'
        ]);

        fputcsv($csv, [
            'Proveedor S.A.', 'Electrónica', 'Celular XYZ', 'XYZ-2025', 'Smartphone gama alta',
            100, 1500, 1800, 2000, 2100, '2025-12-31', '1234567890123;9876543210123'
        ]);

        fclose($csv);
        exit;
    }

    public function preview(Request $request)
    {
        if ($request->isMethod('post')) {
    
            $request->validate([
                'file' => 'required|mimes:csv,txt,xlsx,xls|max:4096',
            ]);
    
            $extension = $request->file('file')->getClientOriginalExtension();
            $rows = [];
    
            // CSV / TXT
            if (in_array($extension, ['csv', 'txt'])) {
                $file = fopen($request->file('file'), 'r');
                $header = fgetcsv($file);
    
                while ($data = fgetcsv($file)) {
                    if (count($data) < 11) continue;
    
                    $data = array_pad($data, 12, null);
                    $rows[] = array_combine([
                        'nombre_proveedor', 'nombre_categoria', 'nombre_articulo', 'modelo', 'descripcion',
                        'cantidad', 'costo', 'precio_mayor', 'precio_tienda', 'precio_factura',
                        'fecha_vencimiento', 'codigo_barra'
                    ], $data);
                }
                fclose($file);
    
            // XLSX / XLS
            } elseif (in_array($extension, ['xlsx', 'xls'])) {
                $rows = Excel::toArray([], $request->file('file'))[0];
                $header = array_map('strtolower', array_map(fn($h) => str_replace(' ', '_', $h), $rows[0] ?? []));
                unset($rows[0]);
                $rows = array_values($rows);
    
                $rows = array_filter($rows, fn($r) => count($r) >= 11);
                $rows = array_map(function ($r) {
                    $r = array_pad($r, 12, null);
                    return array_combine([
                        'nombre_proveedor', 'nombre_categoria', 'nombre_articulo', 'modelo', 'descripcion',
                        'cantidad', 'costo', 'precio_mayor', 'precio_tienda', 'precio_factura',
                        'fecha_vencimiento', 'codigo_barra'
                    ], $r);
                }, $rows);
            }
    
            if (count($rows) > 1000) {
                // ✅ Redirigir al formulario con error bonito
                return redirect()->route('entries.importForm')->with('error', 'Solo se permiten máximo 1000 registros.');
            }
    
            $warnings = [];
            $errors = [];
    
            foreach ($rows as $index => $row) {
                $line = $index + 2;
    
                if (empty($row['nombre_proveedor']))    $errors[] = "Línea {$line}: Falta nombre_proveedor.";
                if (empty($row['nombre_categoria']))    $errors[] = "Línea {$line}: Falta nombre_categoria.";
                if (empty($row['nombre_articulo']))     $errors[] = "Línea {$line}: Falta nombre_articulo.";
    
                if (!is_numeric($row['cantidad']) || $row['cantidad'] <= 0)         $errors[] = "Línea {$line}: Cantidad inválida.";
                if (!is_numeric($row['costo']) || $row['costo'] < 0)                $errors[] = "Línea {$line}: Costo inválido.";
                if (!is_numeric($row['precio_mayor']) || $row['precio_mayor'] < 0) $errors[] = "Línea {$line}: Precio mayor inválido.";
                if (!is_numeric($row['precio_tienda']) || $row['precio_tienda'] < 0) $errors[] = "Línea {$line}: Precio tienda inválido.";
                if (!is_numeric($row['precio_factura']) || $row['precio_factura'] < 0) $errors[] = "Línea {$line}: Precio factura inválido.";
    
                if (empty($row['codigo_barra'])) {
                    $warnings[] = "Línea {$line}: Sin códigos de barras (permitido).";
                } else {
                    foreach (explode(';', $row['codigo_barra']) as $barcode) {
                        $barcode = trim($barcode);
                        if (!empty($barcode) && !is_numeric($barcode)) {
                            $errors[] = "Línea {$line}: Código de barra '{$barcode}' inválido.";
                        }
                    }
                }
            }
    
            session([
                'import_rows' => array_values($rows),
                'import_errors' => $errors,
                'import_warnings' => $warnings
            ]);
    
            return redirect()->route('entries.preview', ['page' => 1]);
        }
    
        $rows = session('import_rows', []);
        $errors = session('import_errors', []);
        $warnings = session('import_warnings', []);
    
        $perPage = 100;
        $totalRows = count($rows);
        $totalPages = max(1, ceil($totalRows / $perPage));
        $currentPage = max(1, min($request->get('page', 1), $totalPages));
        $offset = ($currentPage - 1) * $perPage;
        $rowsPaginated = array_slice($rows, $offset, $perPage);
    
        return view('entries.preview', compact('rows', 'errors', 'warnings', 'rowsPaginated', 'currentPage', 'totalPages', 'totalRows'));
    }
    

    public function confirm(Request $request)
    {
        $rows = json_decode($request->input('rows'), true);

        DB::transaction(function() use ($rows) {
            $grouped = collect($rows)->groupBy('nombre_proveedor');

            foreach ($grouped as $providerName => $providerRows) {

                $provider = Person::firstOrCreate(['name' => $providerName, 'type' => 'provider']);
                $entry = Entry::create([
                    'provider_id' => $provider->id,
                    'date' => now(),
                    'total' => 0,
                ]);

                $total = 0;

                foreach($providerRows as $row) {

                    $category = Category::firstOrCreate(['name' => $row['nombre_categoria']], ['status' => 1]);

                    $article = Article::firstOrCreate([
                        'name' => $row['nombre_articulo'],
                        'category_id' => $category->id,
                    ], [
                        'model' => $row['modelo'],
                        'description' => $row['descripcion'],
                        'stock' => 0,
                        'cost' => 0,
                        'wholesale_price' => 0,
                        'store_price' => 0,
                        'invoice_price' => 0,
                        'expiration_date' => !empty($row['fecha_vencimiento']) ? Carbon::parse($row['fecha_vencimiento'])->format('Y-m-d') : null,
                        'status' => 1,
                    ]);

                    if (!empty($row['codigo_barra'])) {
                        foreach(explode(';', $row['codigo_barra']) as $barcode) {
                            ArticleBarcode::firstOrCreate([
                                'article_id' => $article->id,
                                'barcode' => trim($barcode)
                            ]);
                        }
                    }

                    EntryDetail::create([
                        'entry_id' => $entry->id,
                        'article_id' => $article->id,
                        'quantity' => $row['cantidad'],
                        'price' => $row['costo'],
                        'wholesale_price' => $row['precio_mayor'],
                        'store_price' => $row['precio_tienda'],
                        'invoice_price' => $row['precio_factura'],
                    ]);

                    PriceHistory::create([
                        'entry_id' => $entry->id,
                        'article_id' => $article->id,
                        'old_cost' => $article->cost,
                        'new_cost' => $row['costo'],
                        'old_wholesale_price' => $article->wholesale_price,
                        'new_wholesale_price' => $row['precio_mayor'],
                        'old_store_price' => $article->store_price,
                        'new_store_price' => $row['precio_tienda'],
                        'old_invoice_price' => $article->invoice_price,
                        'new_invoice_price' => $row['precio_factura'],
                        'changed_at' => now(),
                    ]);

                    $article->update([
                        'stock' => $article->stock + $row['cantidad'],
                        'cost' => $row['costo'],
                        'wholesale_price' => $row['precio_mayor'],
                        'store_price' => $row['precio_tienda'],
                        'invoice_price' => $row['precio_factura'],
                    ]);

                    $total += $row['cantidad'] * $row['costo'];
                }

                $entry->update(['total' => $total]);
            }
        });

        Alert::success('Éxito', 'Importación finalizada correctamente.');
        return redirect()->route('entries.index');
    }
}
