<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'nombre_rol' => 'JEFE',
                'descripcion' => 'Administrador del sistema con acceso completo',
                'permisos' => json_encode([
                    'deudores' => true,
                    'pagos' => true,
                    'comisiones' => true,
                    'productos' => true,
                    'ventas' => true,
                    'rutas' => true,
                    'usuarios' => true,
                    'reportes' => true,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre_rol' => 'COBRADOR',
                'descripcion' => 'Encargado de cobros y gestión de pagos',
                'permisos' => json_encode([
                    'deudores' => ['ver' => true, 'crear' => false, 'editar' => false],
                    'pagos' => ['registrar' => true, 'ver' => true, 'eliminar' => false],
                    'comisiones' => ['ver' => true],
                    'rutas' => ['ver' => true],
                    'reportes' => ['ver_propios' => true],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre_rol' => 'VENDEDOR',
                'descripcion' => 'Encargado de ventas y gestión de productos',
                'permisos' => json_encode([
                    'productos' => ['ver' => true, 'crear' => false],
                    'ventas' => ['crear' => true, 'ver' => true],
                    'deudores' => ['ver' => true],
                    'reportes' => ['ver_propios' => true],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('roles')->insert($roles);

        $this->command->info('✅ Roles creados: JEFE, COBRADOR, VENDEDOR');
    }
}