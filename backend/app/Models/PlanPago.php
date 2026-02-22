<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanPago extends Model
{
    use HasFactory;

    protected $table = 'planes_pago';
    protected $primaryKey = 'id_plan';

    protected $fillable = [
        'id_venta',
        'modalidad',
        'valor_cuota',
        'cuotas_totales',
        'estado_plan',
    ];

    protected $casts = [
        'valor_cuota' => 'decimal:2',
        'cuotas_totales' => 'integer',
    ];

    /**
     * Relación: Pertenece a una venta
     */
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'id_venta', 'id_venta');
    }

    /**
     * Relación: Tiene muchas cuotas
     */
    public function cuotas()
    {
        return $this->hasMany(Cuota::class, 'id_plan', 'id_plan');
    }

    /**
     * Cuotas pendientes
     */
    public function cuotasPendientes()
    {
        return $this->hasMany(Cuota::class, 'id_plan', 'id_plan')
                    ->where('estado_cuota', 'PENDIENTE');
    }

    /**
     * Cuotas pagadas
     */
    public function cuotasPagadas()
    {
        return $this->hasMany(Cuota::class, 'id_plan', 'id_plan')
                    ->where('estado_cuota', 'PAGADA');
    }

    /**
     * Scope para planes activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado_plan', 'ACTIVO');
    }
}