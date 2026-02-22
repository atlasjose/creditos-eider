<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeudorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Coordenadas en Bogotá (diferentes sectores)
        $coordenadasBogota = [
            ['lat' => 4.648682, 'lng' => -74.089890], // Sur
            ['lat' => 4.668990, 'lng' => -74.057123], // Centro
            ['lat' => 4.652345, 'lng' => -74.098765], // Oeste
            ['lat' => 4.710989, 'lng' => -74.072092], // Norte
            ['lat' => 4.639386, 'lng' => -74.082412], // Sur-Oeste
            ['lat' => 4.678521, 'lng' => -74.048763], // Centro-Este
            ['lat' => 4.695234, 'lng' => -74.061234], // Norte-Centro
            ['lat' => 4.625478, 'lng' => -74.095621], // Sur-Sur
            ['lat' => 4.687432, 'lng' => -74.073456], // Centro-Norte
            ['lat' => 4.642187, 'lng' => -74.086543], // Sur-Centro
        ];

        $deudores = [];
        $tarjetas = [];

        for ($i = 1; $i <= 10; $i++) {
            $coord = $coordenadasBogota[$i - 1];
            $rutaId = (($i - 1) % 3) + 1; // Distribuir entre las 3 rutas
            $diaCobro = (($i - 1) % 7) + 1; // Días 1-7

            $deudores[] = [
                'codigo_deudor' => 'DEU-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'cedula' => '800' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'nombres' => $this->getNombre($i),
                'apellidos' => $this->getApellido($i),
                'direccion' => $this->getDireccion($i),
                'latitud' => $coord['lat'],
                'longitud' => $coord['lng'],
                'telefono_principal' => '300' . str_pad($i, 7, '0', STR_PAD_LEFT),
                'telefono_secundario' => null,
                'id_ruta' => $rutaId,
                'dia_cobro_preferido' => $diaCobro,
                'recordatorio_diario' => true,
                'score_credito' => rand(70, 100),
                'id_creado_por' => 1, // Creado por el JEFE
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Crear tarjeta para cada deudor
            $limiteCredito = rand(500000, 2000000);
            $saldoActual = rand(0, $limiteCredito / 2);

            $tarjetas[] = [
                'codigo_tarjeta' => 'TC-' . strtoupper(substr(md5($i), 0, 10)),
                'id_deudor' => $i,
                'fecha_emision' => now()->subMonths(rand(1, 12)),
                'limite_credito' => $limiteCredito,
                'saldo_actual' => $saldoActual,
                'saldo_vencido' => rand(0, 100000),
                'estado_tarjeta' => 'ACTIVA',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('deudores')->insert($deudores);
        DB::table('tarjetas_deudor')->insert($tarjetas);

        $this->command->info('✅ 10 Deudores creados (todos con coordenadas GPS)');
        $this->command->info('✅ 10 Tarjetas de crédito creadas');
    }

    private function getNombre($index): string
    {
        $nombres = [
            'Luis', 'Sandra', 'Roberto', 'María', 'Carlos',
            'Andrea', 'Jorge', 'Diana', 'Felipe', 'Claudia'
        ];
        return $nombres[$index - 1];
    }

    private function getApellido($index): string
    {
        $apellidos = [
            'Ramírez', 'Torres', 'Díaz', 'García', 'López',
            'Martínez', 'Hernández', 'Gómez', 'Rodríguez', 'Fernández'
        ];
        return $apellidos[$index - 1];
    }

    private function getDireccion($index): string
    {
        $calles = [
            'Calle 45 #12-34', 'Carrera 7 #80-45', 'Avenida 68 #45-90',
            'Calle 100 #20-30', 'Carrera 15 #35-60', 'Calle 26 #50-25',
            'Avenida Boyacá #55-80', 'Calle 53 #10-15', 'Carrera 30 #70-40',
            'Calle 72 #25-50'
        ];
        return $calles[$index - 1] . ', Bogotá';
    }
}