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
        Schema::create('entry_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entry_id')->constrained('entries')->onDelete('cascade');
            $table->foreignId('article_id')->constrained('articles')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->dateTime('created_at')->nullable(); // Reemplazamos 'timestamps' por columnas manuales
            $table->dateTime('updated_at')->nullable(); // Reemplazamos 'timestamps' por columnas manuales
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entry_details');
    }
};
