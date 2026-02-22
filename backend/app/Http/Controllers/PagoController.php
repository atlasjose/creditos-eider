<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Cuota;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller
{
    /**
     * Listar todos los pagos con filtros
     */
    public function index(Request $request)
    {
        $query = Pago::with([
            'cuota.plan.venta.tarjeta.deudor',
            'cobrador'
        ]);

        // Filtro por cobrador
        if ($request->has('id_cobrador')) {
            $query->where('id_cobrador', $request->id_cobrador);
        }

        // Filtro por rango de fechas
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->whereBetween('fecha_pago', [
                $request->fecha_inicio,
                $request->fecha_fin
            ]);
        }

        // Filtro por día específico
        if ($request->has('fecha')) {
            $query->whereDate('fecha_pago', $request->fecha);
        }

        // Filtro por método de pago
        if ($request->has('metodo_pago')) {
            $query->where('metodo_pago', $request->metodo_pago);
        }

        $pagos = $query->orderBy('fecha_pago', 'desc')
                       ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $pagos
        ]);
    }

    /**
     * Registrar un nuevo pago
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_cuota' => 'required|exists:cuotas,id_cuota',
            'monto_abonado' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|in:EFECTIVO,TRANSFERENCIA,DATAFONO,OTRO',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Obtener la cuota
            $cuota = Cuota::findOrFail($request->id_cuota);

            // Verificar que la cuota esté pendiente
            if ($cuota->estado_cuota === 'PAGADA') {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta cuota ya está pagada'
                ], 400);
            }

            // Calcular saldo pendiente
            $totalPagado = $cuota->pagos()->sum('monto_abonado');
            $saldoPendiente = $cuota->monto_cuota - $totalPagado;

            // Validar que no se exceda el monto
            if ($request->monto_abonado > $saldoPendiente) {
                return response()->json([
                    'success' => false,
                    'message' => "El monto excede el saldo pendiente de $saldoPendiente"
                ], 400);
            }

            // Obtener el cobrador (usuario autenticado)
            $idCobrador = $request->id_cobrador ?? auth()->id();

            // Registrar el pago
            $pago = Pago::create([
                'id_cuota' => $request->id_cuota,
                'id_cobrador' => $idCobrador,
                'monto_abonado' => $request->monto_abonado,
                'fecha_pago' => now(),
                'metodo_pago' => $request->metodo_pago,
                // La comisión se calcula automáticamente en el modelo
            ]);

            // Verificar si la cuota quedó completamente pagada
            $nuevoTotalPagado = $cuota->pagos()->sum('monto_abonado');
            if ($nuevoTotalPagado >= $cuota->monto_cuota) {
                $cuota->update(['estado_cuota' => 'PAGADA']);
            }

            // Actualizar saldo de la tarjeta
            $plan = $cuota->plan;
            $venta = $plan->venta;
            $tarjeta = $venta->tarjeta;

            $tarjeta->decrement('saldo_actual', $request->monto_abonado);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pago registrado exitosamente',
                'data' => $pago->load(['cuota', 'cobrador'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar pago: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener un pago específico
     */
    public function show($id)
    {
        $pago = Pago::with([
            'cuota.plan.venta.tarjeta.deudor',
            'cobrador'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $pago
        ]);
    }

    /**
     * Obtener pagos de un cobrador específico
     */
    public function porCobrador($id)
    {
        $cobrador = Usuario::findOrFail($id);

        $pagos = Pago::with(['cuota.plan.venta.tarjeta.deudor'])
                    ->where('id_cobrador', $id)
                    ->orderBy('fecha_pago', 'desc')
                    ->paginate(15);

        // Calcular estadísticas
        $estadisticas = [
            'total_recaudado_hoy' => Pago::where('id_cobrador', $id)
                                         ->whereDate('fecha_pago', today())
                                         ->sum('monto_abonado'),
            'total_recaudado_mes' => Pago::where('id_cobrador', $id)
                                         ->whereMonth('fecha_pago', now()->month)
                                         ->whereYear('fecha_pago', now()->year)
                                         ->sum('monto_abonado'),
            'comision_generada_mes' => Pago::where('id_cobrador', $id)
                                          ->whereMonth('fecha_pago', now()->month)
                                          ->whereYear('fecha_pago', now()->year)
                                          ->sum('comision_generada'),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'pagos' => $pagos,
                'estadisticas' => $estadisticas,
                'cobrador' => $cobrador
            ]
        ]);
    }

    /**
     * Obtener resumen de pagos del día
     */
    public function resumenDia(Request $request)
    {
        $fecha = $request->fecha ?? today();
        $idCobrador = $request->id_cobrador ?? auth()->id();

        $pagos = Pago::with(['cuota.plan.venta.tarjeta.deudor'])
                    ->where('id_cobrador', $idCobrador)
                    ->whereDate('fecha_pago', $fecha)
                    ->get();

        $resumen = [
            'fecha' => $fecha,
            'total_pagos' => $pagos->count(),
            'total_recaudado' => $pagos->sum('monto_abonado'),
            'comision_generada' => $pagos->sum('comision_generada'),
            'metodos_pago' => $pagos->groupBy('metodo_pago')->map(function($items) {
                return [
                    'cantidad' => $items->count(),
                    'monto' => $items->sum('monto_abonado')
                ];
            }),
            'pagos' => $pagos
        ];

        return response()->json([
            'success' => true,
            'data' => $resumen
        ]);
    }

    /**
     * Eliminar un pago (solo si es del día y el usuario es jefe)
     */
    public function destroy($id)
    {
        $pago = Pago::findOrFail($id);

        // Verificar que sea del día actual
        if (!$pago->fecha_pago->isToday()) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden eliminar pagos del día actual'
            ], 403);
        }

        DB::beginTransaction();

        try {
            // Actualizar saldo de tarjeta
            $cuota = $pago->cuota;
            $tarjeta = $cuota->plan->venta->tarjeta;
            $tarjeta->increment('saldo_actual', $pago->monto_abonado);

            // Actualizar estado de cuota si estaba pagada
            if ($cuota->estado_cuota === 'PAGADA') {
                $cuota->update(['estado_cuota' => 'PENDIENTE']);
            }

            // Eliminar pago
            $pago->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pago eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar pago: ' . $e->getMessage()
            ], 500);
        }
    }
}