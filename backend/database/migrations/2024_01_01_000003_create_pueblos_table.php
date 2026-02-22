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
        Schema::create('pueblos', function (Blueprint $table) {
            $table->id('id_pueblo');
            $table->string('nombre_pueblo', 50)->unique();
            $table->string('departamento', 50)->nullable();
            $table->decimal('latitud', 9, 6)->nullable();
            $table->decimal('longitud', 9, 6)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            // Índices
            $table->index('nombre_pueblo');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pueblos');
    }
};