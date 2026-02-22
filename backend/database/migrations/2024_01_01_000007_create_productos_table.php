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
        Schema::create('productos', function (Blueprint $table) {
            $table->id('id_producto');
            $table->string('codigo_producto', 50)->unique();
            $table->string('nombre_producto', 100);
            $table->text('descripcion')->nullable();
            $table->decimal('precio_venta', 12, 2);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            // Índices
            $table->index('codigo_producto');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};