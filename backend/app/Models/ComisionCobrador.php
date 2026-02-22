<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComisionCobrador extends Model
{
    use HasFactory;

    protected $table = 'comisiones_cobradores';
    protected $primaryKey = 'id_comision';

    protected $fillable = [
        'id_cobrador',
        'fecha_inicio',
        'fecha_fin',
        'total_recaudado',
        'total_comision',
        'estado_pago',
        'fecha_pago',
        'id_jefe_pagador',
        'periodicidad',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_pago' => 'date',
        'total_recaudado' => 'decimal:2',
        'total_comision' => 'decimal:2',
    ];

    /**
     * Relación: Pertenece a un cobrador
     */
    public function cobrador()
    {
        return $this->belongsTo(Usuario::class, 'id_cobrador', 'id_usuario');
    }

    /**
     * Relación: Fue pagada por un jefe
     */
    public function jefePagador()
    {
        return $this->belongsTo(Usuario::class, 'id_jefe_pagador', 'id_usuario');
    }

    /**
     * Scope para comisiones pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado_pago', 'PENDIENTE');
    }

    /**
     * Scope para comisiones pagadas
     */
    public function scopePagadas($query)
    {
        return $query->where('estado_pago', 'PAGADO');
    }

    /**
     * Scope para comisiones de un cobrador
     */
    public function scopeDelCobrador($query, $idCobrador)
    {
        return $query->where('id_cobrador', $idCobrador);
    }
}