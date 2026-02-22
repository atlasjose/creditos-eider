<?php

namespace App\Services;

use App\Models\ComisionCobrador;
use App\Models\Pago;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class ComisionService
{
    /**
     * Calcular comisión para un cobrador en un período.
     *
     * @param  array  $data  ['id_cobrador', 'fecha_inicio', 'fecha_fin', 'periodicidad']
     * @return array
     */
    public function calcularComision(array $data): array
    {
        DB::beginTransaction();

        try {
            $cobrador = Usuario::findOrFail($data['id_cobrador']);

            // Verificar que el usuario sea cobrador
            if (!$cobrador->isCobrador()) {
                return [
                    'success' => false,
                    'message' => 'El usuario no es un cobrador'
                ];
            }

            // Verificar que no exista ya una comisión para este período
            $existe = ComisionCobrador::where('id_cobrador', $data['id_cobrador'])
                                     ->where('fecha_inicio', $data['fecha_inicio'])
                                     ->where('fecha_fin', $data['fecha_fin'])
                                     ->exists();

            if ($existe) {
                return [
                    'success' => false,
                    'message' => 'Ya existe una comisión calculada para este período'
                ];
            }

            // Obtener pagos del período
            $pagos = Pago::where('id_cobrador', $data['id_cobrador'])
                        ->whereBetween('fecha_pago', [
                            $data['fecha_inicio'],
                            $data['fecha_fin'] . ' 23:59:59'
                        ])
                        ->get();

            if ($pagos->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No hay pagos registrados en este período'
                ];
            }

            // Calcular totales
            $totalRecaudado = $pagos->sum('monto_abonado');
            $totalComision = $pagos->sum('comision_generada');

            // Crear registro de comisión
            $comision = ComisionCobrador::create([
                'id_cobrador' => $data['id_cobrador'],
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin' => $data['fecha_fin'],
                'total_recaudado' => $totalRecaudado,
                'total_comision' => $totalComision,
                'estado_pago' => 'PENDIENTE',
                'periodicidad' => $data['periodicidad'],
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Comisión calculada exitosamente',
                'data' => $comision->load('cobrador')
            ];

        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al calcular comisión: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Calcular comisiones automáticamente para todos los cobradores.
     *
     * @param  string  $periodicidad  SEMANAL, QUINCENAL, MENSUAL
     * @return array
     */
    public function calcularAutomatico(string $periodicidad): array
    {
        // Calcular fechas según periodicidad
        $fechaFin = Carbon::today();
        
        switch ($periodicidad) {
            case 'SEMANAL':
                $fechaInicio = $fechaFin->copy()->subWeek();
                break;
            case 'QUINCENAL':
                $fechaInicio = $fechaFin->copy()->subDays(15);
                break;
            case 'MENSUAL':
                $fechaInicio = $fechaFin->copy()->subMonth();
                break;
            default:
                return [
                    'success' => false,
                    'message' => 'Periodicidad no válida'
                ];
        }

        // Obtener todos los cobradores activos
        $cobradores = Usuario::where('id_rol', 2) // Rol COBRADOR
                            ->where('activo', true)
                            ->get();

        $comisionesCreadas = [];
        $errores = [];

        DB::beginTransaction();

        try {
            foreach ($cobradores as $cobrador) {
                // Verificar si ya existe comisión para este período
                $existe = ComisionCobrador::where('id_cobrador', $cobrador->id_usuario)
                                         ->where('fecha_inicio', $fechaInicio)
                                         ->where('fecha_fin', $fechaFin)
                                         ->exists();

                if ($existe) {
                    continue;
                }

                // Obtener pagos del período
                $pagos = Pago::where('id_cobrador', $cobrador->id_usuario)
                            ->whereBetween('fecha_pago', [
                                $fechaInicio,
                                $fechaFin . ' 23:59:59'
                            ])
                            ->get();

                if ($pagos->isEmpty()) {
                    continue;
                }

                // Calcular totales
                $totalRecaudado = $pagos->sum('monto_abonado');
                $totalComision = $pagos->sum('comision_generada');

                // Crear comisión
                $comision = ComisionCobrador::create([
                    'id_cobrador' => $cobrador->id_usuario,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'total_recaudado' => $totalRecaudado,
                    'total_comision' => $totalComision,
                    'estado_pago' => 'PENDIENTE',
                    'periodicidad' => $periodicidad,
                ]);

                $comisionesCreadas[] = $comision;
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Comisiones calculadas automáticamente',
                'data' => [
                    'comisiones_creadas' => count($comisionesCreadas),
                    'periodo' => [
                        'inicio' => $fechaInicio->format('Y-m-d'),
                        'fin' => $fechaFin->format('Y-m-d'),
                        'periodicidad' => $periodicidad
                    ],
                    'comisiones' => $comisionesCreadas
                ]
            ];

        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al calcular comisiones: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Marcar una comisión como pagada.
     *
     * @param  int  $idComision
     * @param  int  $idJefePagador
     * @param  string  $fechaPago
     * @return array
     */
    public function marcarPagada(int $idComision, int $idJefePagador, string $fechaPago): array
    {
        try {
            $comision = ComisionCobrador::findOrFail($idComision);

            if ($comision->estado_pago === 'PAGADO') {
                return [
                    'success' => false,
                    'message' => 'Esta comisión ya está marcada como pagada'
                ];
            }

            $comision->update([
                'estado_pago' => 'PAGADO',
                'fecha_pago' => $fechaPago,
                'id_jefe_pagador' => $idJefePagador,
            ]);

            return [
                'success' => true,
                'message' => 'Comisión marcada como pagada',
                'data' => $comision->load(['cobrador', 'jefePagador'])
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al marcar comisión como pagada: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener estadísticas de comisiones de un cobrador.
     *
     * @param  int  $idCobrador
     * @return array
     */
    public function estadisticasCobrador(int $idCobrador): array
    {
        $comisiones = ComisionCobrador::where('id_cobrador', $idCobrador)->get();

        return [
            'total_pendiente' => $comisiones->where('estado_pago', 'PENDIENTE')->sum('total_comision'),
            'total_pagado' => $comisiones->where('estado_pago', 'PAGADO')->sum('total_comision'),
            'total_general' => $comisiones->sum('total_comision'),
            'total_recaudado' => $comisiones->sum('total_recaudado'),
            'comisiones_pendientes' => $comisiones->where('estado_pago', 'PENDIENTE')->count(),
            'comisiones_pagadas' => $comisiones->where('estado_pago', 'PAGADO')->count(),
        ];
    }

    /**
     * Calcular el porcentaje de comisión promedio de un cobrador.
     *
     * @param  int  $idCobrador
     * @return float
     */
    public function calcularPorcentajePromedio(int $idCobrador): float
    {
        $cobrador = Usuario::find($idCobrador);
        
        if (!$cobrador) {
            return 0.0;
        }

        return $cobrador->porcentaje_comision ?? 10.0;
    }

    /**
     * Obtener ranking de cobradores por comisiones.
     *
     * @param  string|null  $periodo  SEMANAL, MENSUAL, ANUAL
     * @return array
     */
    public function rankingCobradores(?string $periodo = 'MENSUAL'): array
    {
        $query = DB::table('pagos')
                  ->join('usuarios', 'pagos.id_cobrador', '=', 'usuarios.id_usuario')
                  ->select(
                      'usuarios.id_usuario',
                      'usuarios.nombres',
                      'usuarios.apellidos',
                      DB::raw('COUNT(pagos.id_pago) as total_pagos'),
                      DB::raw('SUM(pagos.monto_abonado) as total_recaudado'),
                      DB::raw('SUM(pagos.comision_generada) as total_comision')
                  )
                  ->where('usuarios.id_rol', 2); // Solo cobradores

        // Filtrar por período
        switch ($periodo) {
            case 'SEMANAL':
                $query->where('pagos.fecha_pago', '>=', now()->subWeek());
                break;
            case 'MENSUAL':
                $query->whereMonth('pagos.fecha_pago', now()->month)
                      ->whereYear('pagos.fecha_pago', now()->year);
                break;
            case 'ANUAL':
                $query->whereYear('pagos.fecha_pago', now()->year);
                break;
        }

        $ranking = $query->groupBy('usuarios.id_usuario', 'usuarios.nombres', 'usuarios.apellidos')
                        ->orderBy('total_recaudado', 'desc')
                        ->get();

        return [
            'periodo' => $periodo,
            'ranking' => $ranking
        ];
    }
}