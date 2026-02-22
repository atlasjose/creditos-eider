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
        Schema::create('comisiones_cobradores', function (Blueprint $table) {
            $table->id('id_comision');
            $table->unsignedBigInteger('id_cobrador');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->decimal('total_recaudado', 12, 2)->comment('Suma de pagos en el período');
            $table->decimal('total_comision', 12, 2)->comment('Calculado según % del cobrador');
            $table->string('estado_pago', 20)->default('PENDIENTE')->comment('PENDIENTE, PAGADO');
            $table->date('fecha_pago')->nullable()->comment('Fecha en que el jefe pagó la comisión');
            $table->unsignedBigInteger('id_jefe_pagador')->nullable()->comment('Jefe que realizó el pago');
            $table->string('periodicidad', 20)->nullable()->comment('SEMANAL, QUINCENAL, MENSUAL');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('id_cobrador')->references('id_usuario')->on('usuarios')->onDelete('restrict');
            $table->foreign('id_jefe_pagador')->references('id_usuario')->on('usuarios')->onDelete('set null');
            
            // Índices
            $table->index(['id_cobrador', 'estado_pago'], 'idx_cobrador_estado');
            $table->index('id_cobrador', 'idx_comisiones_cobrador');
            $table->index('id_jefe_pagador', 'idx_comisiones_jefe');
            $table->index(['fecha_inicio', 'fecha_fin'], 'idx_comisiones_periodo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comisiones_cobradores');
    }
};