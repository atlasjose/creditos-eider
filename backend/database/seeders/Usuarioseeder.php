<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener IDs de roles
        $jefeId = DB::table('roles')->where('nombre_rol', 'JEFE')->value('id_rol');
        $cobradorId = DB::table('roles')->where('nombre_rol', 'COBRADOR')->value('id_rol');
        $vendedorId = DB::table('roles')->where('nombre_rol', 'VENDEDOR')->value('id_rol');

        $usuarios = [
            // JEFE
            [
                'cedula' => '1234567890',
                'nombres' => 'Carlos',
                'apellidos' => 'Rodríguez',
                'telefono' => '3001234567',
                'email' => 'admin@creditos.com',
                'clave_hash' => Hash::make('admin123'),
                'id_rol' => $jefeId,
                'id_jefe_asociado' => null,
                'porcentaje_comision' => 0.00,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // COBRADORES
            [
                'cedula' => '1234567891',
                'nombres' => 'Juan',
                'apellidos' => 'Pérez',
                'telefono' => '3001234568',
                'email' => 'cobrador1@creditos.com',
                'clave_hash' => Hash::make('cobrador123'),
                'id_rol' => $cobradorId,
                'id_jefe_asociado' => 1, // Asociado al JEFE
                'porcentaje_comision' => 10.00,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cedula' => '1234567892',
                'nombres' => 'María',
                'apellidos' => 'González',
                'telefono' => '3001234569',
                'email' => 'cobrador2@creditos.com',
                'clave_hash' => Hash::make('cobrador123'),
                'id_rol' => $cobradorId,
                'id_jefe_asociado' => 1,
                'porcentaje_comision' => 12.00,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // VENDEDORES
            [
                'cedula' => '1234567893',
                'nombres' => 'Pedro',
                'apellidos' => 'Martínez',
                'telefono' => '3001234570',
                'email' => 'vendedor1@creditos.com',
                'clave_hash' => Hash::make('vendedor123'),
                'id_rol' => $vendedorId,
                'id_jefe_asociado' => 1,
                'porcentaje_comision' => 0.00,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cedula' => '1234567894',
                'nombres' => 'Ana',
                'apellidos' => 'López',
                'telefono' => '3001234571',
                'email' => 'vendedor2@creditos.com',
                'clave_hash' => Hash::make('vendedor123'),
                'id_rol' => $vendedorId,
                'id_jefe_asociado' => 1,
                'porcentaje_comision' => 0.00,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('usuarios')->insert($usuarios);

        $this->command->info('✅ 5 Usuarios creados (1 JEFE, 2 COBRADORES, 2 VENDEDORES)');
    }
}