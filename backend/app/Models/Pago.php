<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';
    protected $primaryKey = 'id_pago';

    protected $fillable = [
        'id_cuota',
        'id_cobrador',
        'monto_abonado',
        'fecha_pago',
        'metodo_pago',
        'comision_generada',
    ];

    protected $casts = [
        'monto_abonado' => 'decimal:2',
        'comision_generada' => 'decimal:2',
        'fecha_pago' => 'datetime',
    ];

    /**
     * Boot method - Calcular comisión automáticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pago) {
            if (!$pago->comision_generada) {
                $cobrador = $pago->cobrador;
                if ($cobrador) {
                    $pago->comision_generada = ($pago->monto_abonado * $cobrador->porcentaje_comision) / 100;
                }
            }
        });
    }

    /**
     * Relación: Pertenece a una cuota
     */
    public function cuota()
    {
        return $this->belongsTo(Cuota::class, 'id_cuota', 'id_cuota');
    }

    /**
     * Relación: Pertenece a un cobrador
     */
    public function cobrador()
    {
        return $this->belongsTo(Usuario::class, 'id_cobrador', 'id_usuario');
    }

    /**
     * Scope para pagos de un cobrador
     */
    public function scopeDelCobrador($query, $idCobrador)
    {
        return $query->where('id_cobrador', $idCobrador);
    }

    /**
     * Scope para pagos en un rango de fechas
     */
    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_pago', [$fechaInicio, $fechaFin]);
    }

    /**
     * Scope para pagos del día
     */
    public function scopeDelDia($query, $fecha = null)
    {
        $fecha = $fecha ?: now()->format('Y-m-d');
        return $query->whereDate('fecha_pago', $fecha);
    }
}