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
        Schema::create('tarjetas_deudor', function (Blueprint $table) {
            $table->id('id_tarjeta');
            $table->string('codigo_tarjeta', 20)->unique();
            $table->unsignedBigInteger('id_deudor');
            $table->date('fecha_emision');
            $table->decimal('limite_credito', 12, 2)->nullable();
            $table->decimal('saldo_actual', 12, 2)->default(0);
            $table->decimal('saldo_vencido', 12, 2)->default(0);
            $table->string('estado_tarjeta', 20)->default('ACTIVA')->comment('ACTIVA, BLOQUEADA, CANCELADA');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('id_deudor')->references('id_deudor')->on('deudores')->onDelete('cascade');
            
            // Índices
            $table->index(['id_deudor', 'estado_tarjeta'], 'idx_deudor_estado');
            $table->index('id_deudor', 'idx_tarjetas_deudor');
            $table->index('codigo_tarjeta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarjetas_deudor');
    }
};