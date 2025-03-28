<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla 'sales'
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('customer_name');
            $table->decimal('total', 10, 2);
            $table->string('invoice_number')->unique();
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->boolean('is_quotation')->default(true); // Si es true, es una cotización; si es false, es una venta
            $table->dateTime('created_at')->nullable(); // Usamos 'datetime'
            $table->dateTime('updated_at')->nullable(); // Usamos 'datetime'
        });

        // Tabla 'sale_details'
        Schema::create('sale_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->foreignId('article_id')->constrained('articles')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->dateTime('created_at')->nullable(); // Usamos 'datetime'
            $table->dateTime('updated_at')->nullable(); // Usamos 'datetime'
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_details');
        Schema::dropIfExists('sales');
    }
};
