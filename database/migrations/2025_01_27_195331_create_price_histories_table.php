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
        Schema::create('price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->onDelete('cascade');
            $table->foreignId('entry_id')->nullable()->constrained('entries')->onDelete('cascade');
            $table->decimal('old_cost', 10, 2)->nullable();
            $table->decimal('new_cost', 10, 2)->nullable();
            $table->decimal('old_wholesale_price', 10, 2)->nullable();
            $table->decimal('new_wholesale_price', 10, 2)->nullable();
            $table->decimal('old_store_price', 10, 2)->nullable();
            $table->decimal('new_store_price', 10, 2)->nullable();
            $table->decimal('old_invoice_price', 10, 2)->nullable();
            $table->decimal('new_invoice_price', 10, 2)->nullable();
            $table->dateTime('changed_at')->nullable();
            $table->timestamps(); // Reemplaza los manuales created_at y updated_at por convenciones de Laravel

            $table->unique(['entry_id', 'article_id']); // Garantiza único registro por combinación
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_histories');
    }
};
