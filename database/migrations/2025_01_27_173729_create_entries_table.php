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
        Schema::create('entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('people')->onDelete('cascade');
            $table->date('date'); // Fecha específica, sin zona horaria
            $table->decimal('total', 10, 2)->default(0);
            $table->dateTime('created_at')->nullable(); // Reemplazamos 'timestamps' por columnas manuales
            $table->dateTime('updated_at')->nullable(); // Reemplazamos 'timestamps' por columnas manuales
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entries');
    }
};
