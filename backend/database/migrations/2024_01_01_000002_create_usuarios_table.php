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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->string('cedula', 20)->unique();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->unique()->nullable();
            $table->string('clave_hash', 255);
            $table->unsignedBigInteger('id_rol');
            $table->unsignedBigInteger('id_jefe_asociado')->nullable()->comment('Jefe que supervisa a este usuario');
            $table->decimal('porcentaje_comision', 5, 2)->default(10.00)->comment('% de comisión sobre cobros');
            $table->boolean('activo')->default(true);
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('id_rol')->references('id_rol')->on('roles')->onDelete('restrict');
            $table->foreign('id_jefe_asociado')->references('id_usuario')->on('usuarios')->onDelete('set null');
            
            // Índices
            $table->index('id_rol', 'idx_usuarios_rol');
            $table->index('id_jefe_asociado', 'idx_usuarios_jefe');
            $table->index('cedula');
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};