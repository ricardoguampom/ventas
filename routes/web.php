<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\EntryController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\EntryImportController;

// Redirección directa al módulo de ventas
Route::redirect('/', '/sales');

// Autenticación
Auth::routes();

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::middleware(['auth'])->group(function () {

    // ✅ Categorías
    Route::resource('categories', CategoryController::class);

    // ✅ Artículos
    Route::prefix('articles')->name('articles.')->group(function () {
        Route::get('export-csv', [ArticleController::class, 'exportAllCsv'])->name('export.all.csv');
        Route::get('export-pdf', [ArticleController::class, 'exportAllPdf'])->name('export.all.pdf');
        Route::get('investment', [ArticleController::class, 'totalInvestment'])->name('investment');
        Route::get('{article}/export-csv', [ArticleController::class, 'exportCsv'])->name('export.csv');
        Route::get('{article}/export-pdf', [ArticleController::class, 'exportPdf'])->name('export.pdf');
    });
    Route::resource('articles', ArticleController::class);

    // ✅ Clients
    Route::resource('clients', ClientController::class);

    // ✅ Ingresos (Entries)
    Route::prefix('entries')->name('entries.')->group(function () {
        Route::get('reports', [EntryController::class, 'showReportForm'])->name('report.form');
        Route::get('export', [EntryController::class, 'exportEntries'])->name('export');
    });


    // Ruta para formulario de importación
    Route::get('/entries/importar', [EntryImportController::class, 'importForm'])->name('entries.importForm');

    // Ruta para previsualizar antes de confirmar
    Route::match(['get', 'post'], 'entries/preview', [EntryImportController::class, 'preview'])->name('entries.preview');

    // Ruta para confirmar la importación
    Route::post('/entries/confirm', [EntryImportController::class, 'confirm'])->name('entries.confirm');

    // Ruta para descargar la plantilla ya adaptada a español
    Route::get('/entries/plantilla', [EntryImportController::class, 'template'])->name('entries.template');


    // Al final
    Route::resource('entries', EntryController::class);

    // ✅ Ventas
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('reports', [SaleController::class, 'reports'])->name('reports');
        Route::get('export-report', [SaleController::class, 'exportSalesReport'])->name('exportReport');
        Route::get('quotations/export', [SaleController::class, 'exportQuotations'])->name('quotations.export');
        Route::get('pending/export', [SaleController::class, 'exportPendingSales'])->name('pending.export');
        Route::patch('{sale}/update-status', [SaleController::class, 'updateStatus'])->name('updateStatus');
        Route::patch('{sale}/cancel', [SaleController::class, 'cancelSale'])->name('cancel');
        Route::post('{sale}/convert-to-sale', [SaleController::class, 'convertToSale'])->name('convert');
        Route::get('{sale}/export-pdf', [SaleController::class, 'exportPdf'])->name('export.pdf');
    });

    // ✅ Providers
    Route::resource('providers', ProviderController::class);

    Route::resource('sales', SaleController::class);

    // ✅ Usuarios
    Route::resource('users', UserController::class);

    // ✅ Roles
    Route::get('roles/{role}/users', [RoleController::class, 'showUsers'])->name('roles.users');
    Route::resource('roles', RoleController::class);
    
});
