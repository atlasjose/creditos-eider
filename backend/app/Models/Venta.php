<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';
    protected $primaryKey = 'id_venta';

    protected $fillable = [
        'id_tarjeta',
        'id_producto',
        'id_vendedor',
        'cantidad',
        'monto_total',
        'fecha_venta',
        'estado_venta',
    ];

    protected $casts = [
        'monto_total' => 'decimal:2',
        'cantidad' => 'integer',
        'fecha_venta' => 'date',
    ];

    /**
     * Relación: Pertenece a una tarjeta
     */
    public function tarjeta()
    {
        return $this->belongsTo(TarjetaDeudor::class, 'id_tarjeta', 'id_tarjeta');
    }

    /**
     * Relación: Pertenece a un producto
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    /**
     * Relación: Pertenece a un vendedor
     */
    public function vendedor()
    {
        return $this->belongsTo(Usuario::class, 'id_vendedor', 'id_usuario');
    }

    /**
     * Relación: Tiene un plan de pago
     */
    public function planPago()
    {
        return $this->hasOne(PlanPago::class, 'id_venta', 'id_venta');
    }

    /**
     * Scope para ventas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('estado_venta', 'ACTIVA');
    }

    /**
     * Scope para ventas de un vendedor
     */
    public function scopeDelVendedor($query, $idVendedor)
    {
        return $query->where('id_vendedor', $idVendedor);
    }
}