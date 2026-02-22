<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\TarjetaDeudor;
use App\Models\Producto;
use App\Models\PlanPago;
use App\Models\Cuota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VentaController extends Controller
{
    /**
     * Listar todas las ventas
     */
    public function index(Request $request)
    {
        $query = Venta::with([
            'tarjeta.deudor',
            'producto',
            'vendedor',
            'planPago'
        ]);

        // Filtro por vendedor
        if ($request->has('id_vendedor')) {
            $query->where('id_vendedor', $request->id_vendedor);
        }

        // Filtro por tarjeta/deudor
        if ($request->has('id_tarjeta')) {
            $query->where('id_tarjeta', $request->id_tarjeta);
        }

        // Filtro por estado
        if ($request->has('estado_venta')) {
            $query->where('estado_venta', $request->estado_venta);
        }

        // Filtro por rango de fechas
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->whereBetween('fecha_venta', [
                $request->fecha_inicio,
                $request->fecha_fin
            ]);
        }

        $ventas = $query->orderBy('fecha_venta', 'desc')
                       ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $ventas
        ]);
    }

    /**
     * Crear una nueva venta a crédito
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_tarjeta' => 'required|exists:tarjetas_deudor,id_tarjeta',
            'id_producto' => 'required|exists:productos,id_producto',
            'cantidad' => 'required|integer|min:1',
            'modalidad' => 'required|in:DIARIO,SEMANAL,QUINCENAL,MENSUAL',
            'cuotas_totales' => 'required|integer|min:1|max:36',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Obtener tarjeta y producto
            $tarjeta = TarjetaDeudor::findOrFail($request->id_tarjeta);
            $producto = Producto::findOrFail($request->id_producto);

            // Validar que la tarjeta esté activa
            if ($tarjeta->estado_tarjeta !== 'ACTIVA') {
                return response()->json([
                    'success' => false,
                    'message' => 'La tarjeta no está activa'
                ], 400);
            }

            // Calcular monto total
            $montoTotal = $producto->precio_venta * $request->cantidad;

            // Validar límite de crédito
            if (!$tarjeta->tieneCreditoDisponible($montoTotal)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Crédito insuficiente. Disponible: ' . $tarjeta->credito_disponible
                ], 400);
            }

            // Crear la venta
            $venta = Venta::create([
                'id_tarjeta' => $request->id_tarjeta,
                'id_producto' => $request->id_producto,
                'id_vendedor' => $request->id_vendedor ?? auth()->id(),
                'cantidad' => $request->cantidad,
                'monto_total' => $montoTotal,
                'fecha_venta' => now(),
                'estado_venta' => 'ACTIVA',
            ]);

            // Calcular valor de cada cuota
            $valorCuota = round($montoTotal / $request->cuotas_totales, 2);

            // Crear plan de pago
            $planPago = PlanPago::create([
                'id_venta' => $venta->id_venta,
                'modalidad' => $request->modalidad,
                'valor_cuota' => $valorCuota,
                'cuotas_totales' => $request->cuotas_totales,
                'estado_plan' => 'ACTIVO',
            ]);

            // Crear cuotas
            $fechaInicio = Carbon::now();

            for ($i = 1; $i <= $request->cuotas_totales; $i++) {
                // Calcular fecha de vencimiento según modalidad
                $fechaVencimiento = $this->calcularFechaVencimiento(
                    $fechaInicio,
                    $i,
                    $request->modalidad
                );

                // Ajustar última cuota si hay diferencia por redondeo
                $montoCuota = $valorCuota;
                if ($i === $request->cuotas_totales) {
                    $totalCuotasAnteriores = $valorCuota * ($request->cuotas_totales - 1);
                    $montoCuota = $montoTotal - $totalCuotasAnteriores;
                }

                Cuota::create([
                    'id_plan' => $planPago->id_plan,
                    'numero_cuota' => $i,
                    'fecha_vencimiento' => $fechaVencimiento,
                    'monto_cuota' => $montoCuota,
                    'estado_cuota' => 'PENDIENTE',
                ]);
            }

            // Actualizar saldo de la tarjeta
            $tarjeta->increment('saldo_actual', $montoTotal);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta registrada exitosamente',
                'data' => $venta->load(['tarjeta.deudor', 'producto', 'vendedor', 'planPago.cuotas'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar venta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calcular fecha de vencimiento según modalidad
     */
    private function calcularFechaVencimiento($fechaInicio, $numeroCuota, $modalidad)
    {
        $fecha = Carbon::parse($fechaInicio);

        switch ($modalidad) {
            case 'DIARIO':
                return $fecha->addDays($numeroCuota);
            case 'SEMANAL':
                return $fecha->addWeeks($numeroCuota);
            case 'QUINCENAL':
                return $fecha->addDays($numeroCuota * 15);
            case 'MENSUAL':
                return $fecha->addMonths($numeroCuota);
            default:
                return $fecha->addWeeks($numeroCuota);
        }
    }

    /**
     * Obtener una venta específica
     */
    public function show($id)
    {
        $venta = Venta::with([
            'tarjeta.deudor',
            'producto',
            'vendedor',
            'planPago.cuotas.pagos'
        ])->findOrFail($id);

        // Calcular estadísticas de pago
        $cuotas = $venta->planPago->cuotas;
        $estadisticas = [
            'cuotas_pagadas' => $cuotas->where('estado_cuota', 'PAGADA')->count(),
            'cuotas_pendientes' => $cuotas->where('estado_cuota', 'PENDIENTE')->count(),
            'cuotas_vencidas' => $cuotas->where('estado_cuota', 'VENCIDA')->count(),
            'total_pagado' => $cuotas->sum(function($cuota) {
                return $cuota->pagos->sum('monto_abonado');
            }),
            'saldo_pendiente' => $venta->monto_total - $cuotas->sum(function($cuota) {
                return $cuota->pagos->sum('monto_abonado');
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'venta' => $venta,
                'estadisticas' => $estadisticas
            ]
        ]);
    }

    /**
     * Cancelar una venta (solo si no tiene pagos)
     */
    public function cancelar($id)
    {
        $venta = Venta::with('planPago.cuotas.pagos')->findOrFail($id);

        if ($venta->estado_venta === 'CANCELADA') {
            return response()->json([
                'success' => false,
                'message' => 'Esta venta ya está cancelada'
            ], 400);
        }

        // Verificar que no tenga pagos
        $tienePagos = $venta->planPago->cuotas->some(function($cuota) {
            return $cuota->pagos->count() > 0;
        });

        if ($tienePagos) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede cancelar una venta que ya tiene pagos registrados'
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Actualizar estado de venta y plan
            $venta->update(['estado_venta' => 'CANCELADA']);
            $venta->planPago->update(['estado_plan' => 'CANCELADO']);

            // Revertir saldo de tarjeta
            $venta->tarjeta->decrement('saldo_actual', $venta->monto_total);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta cancelada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar venta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener ventas de un vendedor específico
     */
    public function porVendedor($id)
    {
        $ventas = Venta::with(['tarjeta.deudor', 'producto', 'planPago'])
                      ->where('id_vendedor', $id)
                      ->orderBy('fecha_venta', 'desc')
                      ->paginate(15);

        // Estadísticas
        $estadisticas = [
            'total_ventas' => $ventas->total(),
            'ventas_activas' => Venta::where('id_vendedor', $id)
                                    ->where('estado_venta', 'ACTIVA')
                                    ->count(),
            'monto_total_vendido' => Venta::where('id_vendedor', $id)
                                          ->where('estado_venta', 'ACTIVA')
                                          ->sum('monto_total'),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'ventas' => $ventas,
                'estadisticas' => $estadisticas
            ]
        ]);
    }
}