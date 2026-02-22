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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id('id_venta');
            $table->unsignedBigInteger('id_tarjeta');
            $table->unsignedBigInteger('id_producto');
            $table->unsignedBigInteger('id_vendedor');
            $table->integer('cantidad')->default(1);
            $table->decimal('monto_total', 12, 2);
            $table->date('fecha_venta');
            $table->string('estado_venta', 20)->default('ACTIVA')->comment('ACTIVA, CANCELADA');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('id_tarjeta')->references('id_tarjeta')->on('tarjetas_deudor')->onDelete('restrict');
            $table->foreign('id_producto')->references('id_producto')->on('productos')->onDelete('restrict');
            $table->foreign('id_vendedor')->references('id_usuario')->on('usuarios')->onDelete('restrict');
            
            // Índices
            $table->index('id_tarjeta', 'idx_ventas_tarjeta');
            $table->index('id_producto', 'idx_ventas_producto');
            $table->index('id_vendedor', 'idx_ventas_vendedor');
            $table->index('fecha_venta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};