<?php

namespace App\Http\Controllers;

use App\Models\ComisionCobrador;
use App\Models\Pago;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ComisionController extends Controller
{
    /**
     * Listar todas las comisiones
     */
    public function index(Request $request)
    {
        $query = ComisionCobrador::with(['cobrador', 'jefePagador']);

        // Filtro por cobrador
        if ($request->has('id_cobrador')) {
            $query->where('id_cobrador', $request->id_cobrador);
        }

        // Filtro por estado
        if ($request->has('estado_pago')) {
            $query->where('estado_pago', $request->estado_pago);
        }

        // Filtro por periodicidad
        if ($request->has('periodicidad')) {
            $query->where('periodicidad', $request->periodicidad);
        }

        $comisiones = $query->orderBy('fecha_inicio', 'desc')
                           ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $comisiones
        ]);
    }

    /**
     * Calcular comisiones para un cobrador en un período
     */
    public function calcular(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_cobrador' => 'required|exists:usuarios,id_usuario',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'periodicidad' => 'required|in:SEMANAL,QUINCENAL,MENSUAL',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $cobrador = Usuario::findOrFail($request->id_cobrador);

            // Verificar que el usuario sea cobrador
            if (!$cobrador->isCobrador()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario no es un cobrador'
                ], 400);
            }

            // Verificar que no exista ya una comisión para este período
            $existe = ComisionCobrador::where('id_cobrador', $request->id_cobrador)
                                     ->where('fecha_inicio', $request->fecha_inicio)
                                     ->where('fecha_fin', $request->fecha_fin)
                                     ->exists();

            if ($existe) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe una comisión calculada para este período'
                ], 400);
            }

            // Obtener pagos del período
            $pagos = Pago::where('id_cobrador', $request->id_cobrador)
                        ->whereBetween('fecha_pago', [
                            $request->fecha_inicio,
                            $request->fecha_fin . ' 23:59:59'
                        ])
                        ->get();

            if ($pagos->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay pagos registrados en este período'
                ], 400);
            }

            // Calcular totales
            $totalRecaudado = $pagos->sum('monto_abonado');
            $totalComision = $pagos->sum('comision_generada');

            // Crear registro de comisión
            $comision = ComisionCobrador::create([
                'id_cobrador' => $request->id_cobrador,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'total_recaudado' => $totalRecaudado,
                'total_comision' => $totalComision,
                'estado_pago' => 'PENDIENTE',
                'periodicidad' => $request->periodicidad,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Comisión calculada exitosamente',
                'data' => $comision->load('cobrador')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al calcular comisión: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar una comisión como pagada
     */
    public function marcarPagada(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'fecha_pago' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $comision = ComisionCobrador::findOrFail($id);

        if ($comision->estado_pago === 'PAGADO') {
            return response()->json([
                'success' => false,
                'message' => 'Esta comisión ya está marcada como pagada'
            ], 400);
        }

        $comision->update([
            'estado_pago' => 'PAGADO',
            'fecha_pago' => $request->fecha_pago,
            'id_jefe_pagador' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comisión marcada como pagada',
            'data' => $comision->load(['cobrador', 'jefePagador'])
        ]);
    }

    /**
     * Obtener comisiones de un cobrador específico
     */
    public function porCobrador($id)
    {
        $cobrador = Usuario::findOrFail($id);

        $comisiones = ComisionCobrador::where('id_cobrador', $id)
                                     ->orderBy('fecha_inicio', 'desc')
                                     ->get();

        // Estadísticas
        $estadisticas = [
            'total_pendiente' => $comisiones->where('estado_pago', 'PENDIENTE')->sum('total_comision'),
            'total_pagado' => $comisiones->where('estado_pago', 'PAGADO')->sum('total_comision'),
            'total_general' => $comisiones->sum('total_comision'),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'comisiones' => $comisiones,
                'estadisticas' => $estadisticas,
                'cobrador' => $cobrador
            ]
        ]);
    }

    /**
     * Calcular comisiones automáticamente para todos los cobradores
     */
    public function calcularAutomatico(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'periodicidad' => 'required|in:SEMANAL,QUINCENAL,MENSUAL',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Calcular fechas según periodicidad
        $fechaFin = Carbon::today();
        
        switch ($request->periodicidad) {
            case 'SEMANAL':
                $fechaInicio = $fechaFin->copy()->subWeek();
                break;
            case 'QUINCENAL':
                $fechaInicio = $fechaFin->copy()->subDays(15);
                break;
            case 'MENSUAL':
                $fechaInicio = $fechaFin->copy()->subMonth();
                break;
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
                    'periodicidad' => $request->periodicidad,
                ]);

                $comisionesCreadas[] = $comision;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Comisiones calculadas automáticamente',
                'data' => [
                    'comisiones_creadas' => count($comisionesCreadas),
                    'comisiones' => $comisionesCreadas
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al calcular comisiones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener una comisión específica
     */
    public function show($id)
    {
        $comision = ComisionCobrador::with(['cobrador', 'jefePagador'])
                                   ->findOrFail($id);

        // Obtener detalle de pagos del período
        $pagos = Pago::where('id_cobrador', $comision->id_cobrador)
                    ->whereBetween('fecha_pago', [
                        $comision->fecha_inicio,
                        $comision->fecha_fin . ' 23:59:59'
                    ])
                    ->with(['cuota.plan.venta.tarjeta.deudor'])
                    ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'comision' => $comision,
                'pagos' => $pagos
            ]
        ]);
    }
}