<?php

namespace App\Services;

use App\Models\Pago;
use App\Models\Cuota;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Exception;

class PagoService
{
    /**
     * Registrar un nuevo pago.
     *
     * @param  array  $data  Datos del pago
     * @return array  ['success' => bool, 'message' => string, 'data' => Pago|null]
     */
    public function registrarPago(array $data): array
    {
        DB::beginTransaction();

        try {
            // Obtener la cuota
            $cuota = Cuota::findOrFail($data['id_cuota']);

            // Verificar que la cuota esté pendiente
            if ($cuota->estado_cuota === 'PAGADA') {
                return [
                    'success' => false,
                    'message' => 'Esta cuota ya está pagada',
                    'data' => null
                ];
            }

            // Calcular saldo pendiente
            $totalPagado = $cuota->pagos()->sum('monto_abonado');
            $saldoPendiente = $cuota->monto_cuota - $totalPagado;

            // Validar que no se exceda el monto
            if ($data['monto_abonado'] > $saldoPendiente) {
                return [
                    'success' => false,
                    'message' => "El monto excede el saldo pendiente de $saldoPendiente",
                    'data' => null
                ];
            }

            // Registrar el pago
            $pago = Pago::create([
                'id_cuota' => $data['id_cuota'],
                'id_cobrador' => $data['id_cobrador'],
                'monto_abonado' => $data['monto_abonado'],
                'fecha_pago' => now(),
                'metodo_pago' => $data['metodo_pago'],
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

            $tarjeta->decrement('saldo_actual', $data['monto_abonado']);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Pago registrado exitosamente',
                'data' => $pago->load(['cuota', 'cobrador'])
            ];

        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al registrar pago: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Obtener resumen de pagos del día para un cobrador.
     *
     * @param  int  $idCobrador
     * @param  string|null  $fecha
     * @return array
     */
    public function resumenDia(int $idCobrador, ?string $fecha = null): array
    {
        $fecha = $fecha ?? today();

        $pagos = Pago::with(['cuota.plan.venta.tarjeta.deudor'])
                    ->where('id_cobrador', $idCobrador)
                    ->whereDate('fecha_pago', $fecha)
                    ->get();

        return [
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
    }

    /**
     * Obtener estadísticas de un cobrador.
     *
     * @param  int  $idCobrador
     * @return array
     */
    public function estadisticasCobrador(int $idCobrador): array
    {
        return [
            'total_recaudado_hoy' => Pago::where('id_cobrador', $idCobrador)
                                         ->whereDate('fecha_pago', today())
                                         ->sum('monto_abonado'),
            
            'total_recaudado_mes' => Pago::where('id_cobrador', $idCobrador)
                                         ->whereMonth('fecha_pago', now()->month)
                                         ->whereYear('fecha_pago', now()->year)
                                         ->sum('monto_abonado'),
            
            'comision_generada_mes' => Pago::where('id_cobrador', $idCobrador)
                                          ->whereMonth('fecha_pago', now()->month)
                                          ->whereYear('fecha_pago', now()->year)
                                          ->sum('comision_generada'),
            
            'total_pagos_mes' => Pago::where('id_cobrador', $idCobrador)
                                     ->whereMonth('fecha_pago', now()->month)
                                     ->whereYear('fecha_pago', now()->year)
                                     ->count(),
        ];
    }

    /**
     * Eliminar un pago (solo del día actual).
     *
     * @param  int  $idPago
     * @return array
     */
    public function eliminarPago(int $idPago): array
    {
        DB::beginTransaction();

        try {
            $pago = Pago::findOrFail($idPago);

            // Verificar que sea del día actual
            if (!$pago->fecha_pago->isToday()) {
                return [
                    'success' => false,
                    'message' => 'Solo se pueden eliminar pagos del día actual'
                ];
            }

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

            return [
                'success' => true,
                'message' => 'Pago eliminado exitosamente'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al eliminar pago: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validar que un cobrador pueda registrar un pago.
     *
     * @param  int  $idCobrador
     * @param  int  $idCuota
     * @return array
     */
    public function validarPago(int $idCobrador, int $idCuota): array
    {
        try {
            // Verificar que el usuario sea cobrador
            $cobrador = Usuario::findOrFail($idCobrador);
            if (!$cobrador->isCobrador()) {
                return [
                    'success' => false,
                    'message' => 'El usuario no es un cobrador'
                ];
            }

            // Verificar que la cuota exista
            $cuota = Cuota::find($idCuota);
            if (!$cuota) {
                return [
                    'success' => false,
                    'message' => 'La cuota no existe'
                ];
            }

            // Verificar que la cuota esté pendiente
            if ($cuota->estado_cuota === 'PAGADA') {
                return [
                    'success' => false,
                    'message' => 'Esta cuota ya está pagada'
                ];
            }

            // Calcular saldo pendiente
            $totalPagado = $cuota->pagos()->sum('monto_abonado');
            $saldoPendiente = $cuota->monto_cuota - $totalPagado;

            return [
                'success' => true,
                'message' => 'Validación exitosa',
                'data' => [
                    'cuota' => $cuota,
                    'saldo_pendiente' => $saldoPendiente,
                    'total_pagado' => $totalPagado
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error en validación: ' . $e->getMessage()
            ];
        }
    }
}