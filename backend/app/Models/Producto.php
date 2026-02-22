<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';
    protected $primaryKey = 'id_producto';

    protected $fillable = [
        'codigo_producto',
        'nombre_producto',
        'descripcion',
        'precio_venta',
        'activo',
    ];

    protected $casts = [
        'precio_venta' => 'decimal:2',
        'activo' => 'boolean',
    ];

    /**
     * Relación: Tiene muchas ventas
     */
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'id_producto', 'id_producto');
    }

    /**
     * Scope para productos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}