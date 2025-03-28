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
        // 🔹 Tabla de artículos
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('name');
            $table->string('model')->nullable(); // 🔹 Modelo ingresado manualmente
            $table->text('description')->nullable();
            $table->integer('stock')->default(0);
            $table->decimal('cost', 10, 2)->default(0); // Precio de compra
            $table->decimal('wholesale_price', 10, 2)->default(0); // Precio al por mayor
            $table->decimal('store_price', 10, 2)->default(0); // Precio de tienda
            $table->decimal('invoice_price', 10, 2)->default(0); // Precio con factura
            $table->date('expiration_date')->nullable();
            $table->boolean('status')->default(1);
            $table->string('image')->nullable(); // Imagen del artículo
            $table->dateTime('created_at')->nullable(); // Cambiamos 'timestamps' por columnas manuales
            $table->dateTime('updated_at')->nullable(); // Cambiamos 'timestamps' por columnas manuales
        });

        // 🔹 Tabla de códigos de barras
        Schema::create('article_barcodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->onDelete('cascade');
            $table->string('barcode')->unique(); // Código de barras único
            $table->dateTime('created_at')->nullable(); // Cambiamos 'timestamps' por columnas manuales
            $table->dateTime('updated_at')->nullable(); // Cambiamos 'timestamps' por columnas manuales
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_barcodes');
        Schema::dropIfExists('articles');
    }
};
