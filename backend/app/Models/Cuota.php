<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Cuota extends Model
{
    use HasFactory;

    protected $table = 'cuotas';
    protected $primaryKey = 'id_cuota';

    protected $fillable = [
        'id_plan',
        'numero_cuota',
        'fecha_vencimiento',
        'monto_cuota',
        'estado_cuota',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'monto_cuota' => 'decimal:2',
    ];

    /**
     * Relación: Pertenece a un plan de pago
     */
    public function plan()
    {
        return $this->belongsTo(PlanPago::class, 'id_plan', 'id_plan');
    }

    /**
     * Relación: Tiene muchos pagos
     */
    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_cuota', 'id_cuota');
    }

    /**
     * Verificar si está vencida
     */
    public function getEstaVencidaAttribute()
    {
        return $this->estado_cuota !== 'PAGADA' && 
               $this->fecha_vencimiento && 
               Carbon::parse($this->fecha_vencimiento)->isPast();
    }

    /**
     * Total pagado en esta cuota
     */
    public function getTotalPagadoAttribute()
    {
        return $this->pagos()->sum('monto_abonado');
    }

    /**
     * Saldo pendiente
     */
    public function getSaldoPendienteAttribute()
    {
        return $this->monto_cuota - $this->total_pagado;
    }

    /**
     * Scope para cuotas pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado_cuota', 'PENDIENTE');
    }

    /**
     * Scope para cuotas vencidas
     */
    public function scopeVencidas($query)
    {
        return $query->where('estado_cuota', 'PENDIENTE')
                     ->where('fecha_vencimiento', '<', now());
    }

    /**
     * Scope para cuotas del día
     */
    public function scopeDelDia($query, $fecha = null)
    {
        $fecha = $fecha ?: now()->format('Y-m-d');
        return $query->whereDate('fecha_vencimiento', $fecha);
    }
}