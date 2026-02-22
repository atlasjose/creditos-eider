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
        Schema::create('rutas_cobro', function (Blueprint $table) {
            $table->id('id_ruta');
            $table->string('nombre_ruta', 50);
            $table->unsignedBigInteger('id_pueblo');
            $table->unsignedBigInteger('id_cobrador_asignado')->nullable();
            $table->boolean('activa')->default(true);
            $table->date('fecha_asignacion')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('id_pueblo')->references('id_pueblo')->on('pueblos')->onDelete('restrict');
            $table->foreign('id_cobrador_asignado')->references('id_usuario')->on('usuarios')->onDelete('set null');
            
            // Índices
            $table->index('id_pueblo', 'idx_rutas_pueblo');
            $table->index('id_cobrador_asignado', 'idx_rutas_cobrador');
            $table->index('activa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rutas_cobro');
    }
};