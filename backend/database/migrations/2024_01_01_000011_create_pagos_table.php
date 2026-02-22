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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id('id_pago');
            $table->unsignedBigInteger('id_cuota');
            $table->unsignedBigInteger('id_cobrador');
            $table->decimal('monto_abonado', 12, 2);
            $table->timestamp('fecha_pago')->useCurrent();
            $table->string('metodo_pago', 20)->nullable()->comment('EFECTIVO, TRANSFERENCIA, etc.');
            $table->decimal('comision_generada', 12, 2)->nullable()->comment('Calculado automáticamente');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('id_cuota')->references('id_cuota')->on('cuotas')->onDelete('restrict');
            $table->foreign('id_cobrador')->references('id_usuario')->on('usuarios')->onDelete('restrict');
            
            // Índices
            $table->index('id_cuota', 'idx_pagos_cuota');
            $table->index('id_cobrador', 'idx_pagos_cobrador');
            $table->index('fecha_pago', 'idx_pagos_fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};