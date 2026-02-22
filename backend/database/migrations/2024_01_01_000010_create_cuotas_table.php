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
        Schema::create('cuotas', function (Blueprint $table) {
            $table->id('id_cuota');
            $table->unsignedBigInteger('id_plan');
            $table->integer('numero_cuota');
            $table->date('fecha_vencimiento')->nullable()->comment('Puede ser modificada por el cobrador');
            $table->decimal('monto_cuota', 12, 2)->nullable()->comment('Puede variar entre cuotas');
            $table->string('estado_cuota', 20)->default('PENDIENTE')->comment('PENDIENTE, PAGADA, VENCIDA');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('id_plan')->references('id_plan')->on('planes_pago')->onDelete('cascade');
            
            // Índices
            $table->index('id_plan', 'idx_cuotas_plan');
            $table->index('estado_cuota', 'idx_cuotas_estado');
            $table->index('fecha_vencimiento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuotas');
    }
};