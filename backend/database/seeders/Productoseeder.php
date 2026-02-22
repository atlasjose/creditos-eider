<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productos = [
            [
                'codigo_producto' => 'PROD-001',
                'nombre_producto' => 'Celular Samsung Galaxy A54',
                'descripcion' => 'Smartphone Android 128GB',
                'precio_venta' => 1200000,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo_producto' => 'PROD-002',
                'nombre_producto' => 'Tablet Lenovo 10"',
                'descripcion' => 'Tablet Android 64GB',
                'precio_venta' => 450000,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo_producto' => 'PROD-003',
                'nombre_producto' => 'TV LG 43" Smart',
                'descripcion' => 'Televisor Smart TV Full HD',
                'precio_venta' => 1800000,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo_producto' => 'PROD-004',
                'nombre_producto' => 'Lavadora Haceb 12kg',
                'descripcion' => 'Lavadora automática carga superior',
                'precio_venta' => 1500000,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo_producto' => 'PROD-005',
                'nombre_producto' => 'Nevera Mabe 250L',
                'descripcion' => 'Refrigerador No Frost',
                'precio_venta' => 2200000,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('productos')->insert($productos);

        $this->command->info('✅ 5 Productos creados');
    }
}