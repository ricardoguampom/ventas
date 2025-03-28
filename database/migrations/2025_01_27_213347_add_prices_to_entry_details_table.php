<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('entry_details', function (Blueprint $table) {
            // Agregamos nuevas columnas para los precios
            $table->decimal('wholesale_price', 10, 2)->after('price')->default(0);
            $table->decimal('store_price', 10, 2)->after('wholesale_price')->default(0);
            $table->decimal('invoice_price', 10, 2)->after('store_price')->default(0);
            // Aseguramos que las fechas se definan explícitamente con datetime
            $table->dateTime('created_at')->nullable()->change();
            $table->dateTime('updated_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entry_details', function (Blueprint $table) {
            // Eliminamos las columnas añadidas
            $table->dropColumn(['wholesale_price', 'store_price', 'invoice_price']);
            // No cambiamos las columnas de fechas para evitar pérdida de datos
        });
    }
};
