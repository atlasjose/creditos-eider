<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RutaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rutas = [
            [
                'nombre_ruta' => 'Ruta Centro Bogotá',
                'id_pueblo' => 1, // Bogotá
                'id_cobrador_asignado' => 2, // Juan Pérez (cobrador1)
                'activa' => true,
                'fecha_asignacion' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre_ruta' => 'Ruta Norte Bogotá',
                'id_pueblo' => 1, // Bogotá
                'id_cobrador_asignado' => 3, // María González (cobrador2)
                'activa' => true,
                'fecha_asignacion' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre_ruta' => 'Ruta Sur Bogotá',
                'id_pueblo' => 1, // Bogotá
                'id_cobrador_asignado' => 2, // Juan Pérez
                'activa' => true,
                'fecha_asignacion' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('rutas_cobro')->insert($rutas);

        $this->command->info('✅ 3 Rutas de cobro creadas y asignadas');
    }
}