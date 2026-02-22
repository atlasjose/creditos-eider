<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TarjetaDeudor extends Model
{
    use HasFactory;

    protected $table = 'tarjetas_deudor';
    protected $primaryKey = 'id_tarjeta';

    protected $fillable = [
        'codigo_tarjeta',
        'id_deudor',
        'fecha_emision',
        'limite_credito',
        'saldo_actual',
        'saldo_vencido',
        'estado_tarjeta',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'limite_credito' => 'decimal:2',
        'saldo_actual' => 'decimal:2',
        'saldo_vencido' => 'decimal:2',
    ];

    /**
     * Relación: Pertenece a un deudor
     */
    public function deudor()
    {
        return $this->belongsTo(Deudor::class, 'id_deudor', 'id_deudor');
    }

    /**
     * Relación: Tiene muchas ventas
     */
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'id_tarjeta', 'id_tarjeta');
    }

    /**
     * Calcular crédito disponible
     */
    public function getCreditoDisponibleAttribute()
    {
        return $this->limite_credito - $this->saldo_actual;
    }

    /**
     * Verificar si tiene crédito disponible
     */
    public function tieneCreditoDisponible($monto)
    {
        return $this->credito_disponible >= $monto;
    }

    /**
     * Scope para tarjetas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('estado_tarjeta', 'ACTIVA');
    }

    /**
     * Scope para tarjetas de un deudor específico
     */
    public function scopeDelDeudor($query, $idDeudor)
    {
        return $query->where('id_deudor', $idDeudor);
    }
}