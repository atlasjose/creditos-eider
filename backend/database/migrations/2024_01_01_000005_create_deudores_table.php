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
        Schema::create('deudores', function (Blueprint $table) {
            $table->id('id_deudor');
            $table->string('codigo_deudor', 20)->unique();
            $table->string('cedula', 20)->unique();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->text('direccion');
            
            // COORDENADAS GPS - NUEVA FUNCIONALIDAD
            $table->decimal('latitud', 9, 6)->nullable()->comment('Coordenada GPS - latitud del domicilio');
            $table->decimal('longitud', 9, 6)->nullable()->comment('Coordenada GPS - longitud del domicilio');
            
            $table->string('telefono_principal', 20);
            $table->unsignedBigInteger('id_ruta');
            $table->tinyInteger('dia_cobro_preferido')->nullable()->comment('1-7 (Lunes-Domingo) o NULL');
            $table->boolean('recordatorio_diario')->default(true)->comment('TRUE si no tiene día establecido');
            $table->smallInteger('score_credito')->default(100);
            $table->unsignedBigInteger('id_creado_por')->nullable()->comment('Usuario (Jefe) que creó este deudor');
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('id_ruta')->references('id_ruta')->on('rutas_cobro')->onDelete('restrict');
            $table->foreign('id_creado_por')->references('id_usuario')->on('usuarios')->onDelete('set null');
            
            // Índices
            $table->index('id_ruta', 'idx_deudores_ruta');
            $table->index('id_creado_por', 'idx_deudores_creado_por');
            $table->index(['latitud', 'longitud'], 'idx_deudores_ubicacion'); // Para búsquedas por proximidad
            $table->index('codigo_deudor');
            $table->index('cedula');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deudores');
    }
};