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
        Schema::create('planes_pago', function (Blueprint $table) {
            $table->id('id_plan');
            $table->unsignedBigInteger('id_venta')->unique();
            $table->string('modalidad', 20)->nullable()->comment('DIARIO, SEMANAL, QUINCENAL, MENSUAL');
            $table->decimal('valor_cuota', 12, 2)->nullable();
            $table->integer('cuotas_totales')->nullable();
            $table->string('estado_plan', 20)->default('ACTIVO')->comment('ACTIVO, COMPLETADO, CANCELADO');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('id_venta')->references('id_venta')->on('ventas')->onDelete('cascade');
            
            // Índices
            $table->index('id_venta', 'idx_planes_venta');
            $table->index('estado_plan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planes_pago');
    }
};