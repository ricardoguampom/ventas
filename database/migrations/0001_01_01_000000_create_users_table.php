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
        // Tabla 'users'
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->dateTime('email_verified_at')->nullable(); // Cambiamos 'timestamp' a 'datetime'
            $table->string('password');
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->rememberToken();
            $table->dateTime('created_at')->nullable(); // Cambiamos 'timestamps' a columnas manuales
            $table->dateTime('updated_at')->nullable();
        });

        // Tabla 'password_reset_tokens'
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->dateTime('created_at')->nullable(); // Cambiamos 'timestamp' a 'datetime'
        });

        // Tabla 'sessions'
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index(); // Este permanece igual porque es un entero
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
