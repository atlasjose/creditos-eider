<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deudor extends Model
{
    use HasFactory;

    protected $table = 'deudores';
    protected $primaryKey = 'id_deudor';

    protected $fillable = [
        'codigo_deudor',
        'cedula',
        'nombres',
        'apellidos',
        'direccion',
        'latitud',        // COORDENADAS GPS
        'longitud',       // COORDENADAS GPS
        'telefono_principal',
        'id_ruta',
        'dia_cobro_preferido',
        'recordatorio_diario',
        'score_credito',
        'id_creado_por',
        'fecha_creacion',
    ];

    protected $casts = [
        'recordatorio_diario' => 'boolean',
        'score_credito' => 'integer',
        'fecha_creacion' => 'datetime',
        'latitud' => 'decimal:6',
        'longitud' => 'decimal:6',
    ];

    protected $appends = ['nombre_completo'];

    /**
     * Nombre completo del deudor
     */
    public function getNombreCompletoAttribute()
    {
        return $this->nombres . ' ' . $this->apellidos;
    }

    /**
     * Verificar si tiene coordenadas GPS
     */
    public function getTieneUbicacionAttribute()
    {
        return !is_null($this->latitud) && !is_null($this->longitud);
    }

    /**
     * Relación: Pertenece a una ruta
     */
    public function ruta()
    {
        return $this->belongsTo(RutaCobro::class, 'id_ruta', 'id_ruta');
    }

    /**
     * Relación: Fue creado por un usuario (jefe)
     */
    public function creador()
    {
        return $this->belongsTo(Usuario::class, 'id_creado_por', 'id_usuario');
    }

    /**
     * Relación: Tiene muchas tarjetas
     */
    public function tarjetas()
    {
        return $this->hasMany(TarjetaDeudor::class, 'id_deudor', 'id_deudor');
    }

    /**
     * Relación: Tarjeta activa
     */
    public function tarjetaActiva()
    {
        return $this->hasOne(TarjetaDeudor::class, 'id_deudor', 'id_deudor')
                    ->where('estado_tarjeta', 'ACTIVA');
    }

    /**
     * Scope para deudores de una ruta específica
     */
    public function scopeDeRuta($query, $idRuta)
    {
        return $query->where('id_ruta', $idRuta);
    }

    /**
     * Scope para deudores con día de cobro específico
     */
    public function scopeConDiaCobro($query, $dia)
    {
        return $query->where('dia_cobro_preferido', $dia);
    }

    /**
     * Scope para deudores con recordatorio diario
     */
    public function scopeConRecordatorio($query)
    {
        return $query->where('recordatorio_diario', true);
    }

    /**
     * Scope para deudores con ubicación GPS
     */
    public function scopeConUbicacion($query)
    {
        return $query->whereNotNull('latitud')
                     ->whereNotNull('longitud');
    }

    /**
     * Scope para buscar deudores cercanos a una coordenada
     * @param $query
     * @param float $lat Latitud de referencia
     * @param float $lng Longitud de referencia
     * @param int $radioKm Radio en kilómetros (default 5km)
     */
    public function scopeCercanos($query, $lat, $lng, $radioKm = 5)
    {
        // Fórmula Haversine simplificada para MySQL
        return $query->whereNotNull('latitud')
                     ->whereNotNull('longitud')
                     ->selectRaw("
                         *, 
                         ( 6371 * acos( cos( radians(?) ) * 
                         cos( radians( latitud ) ) * 
                         cos( radians( longitud ) - radians(?) ) + 
                         sin( radians(?) ) * 
                         sin( radians( latitud ) ) ) ) AS distancia_km
                     ", [$lat, $lng, $lat])
                     ->having('distancia_km', '<=', $radioKm)
                     ->orderBy('distancia_km');
    }
}