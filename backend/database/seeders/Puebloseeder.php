<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PuebloSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pueblos = [
            [
                'nombre_pueblo' => 'Bogotá',
                'departamento' => 'Cundinamarca',
                'latitud' => 4.710989,
                'longitud' => -74.072092,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre_pueblo' => 'Medellín',
                'departamento' => 'Antioquia',
                'latitud' => 6.244203,
                'longitud' => -75.581215,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre_pueblo' => 'Cali',
                'departamento' => 'Valle del Cauca',
                'latitud' => 3.451647,
                'longitud' => -76.531985,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre_pueblo' => 'Barranquilla',
                'departamento' => 'Atlántico',
                'latitud' => 10.968282,
                'longitud' => -74.781302,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre_pueblo' => 'Cartagena',
                'departamento' => 'Bolívar',
                'latitud' => 10.391049,
                'longitud' => -75.479426,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('pueblos')->insert($pueblos);

        $this->command->info('✅ 5 Pueblos creados con coordenadas GPS');
    }
}