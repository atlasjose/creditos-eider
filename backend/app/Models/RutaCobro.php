<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RutaCobro extends Model{
    use HasFactory;
    protected $table= 'rutas_cobro';
    protected $primaryKey= 'id_ruta';

protected $fillable=[
    'nombre_ruta',
    'id_pueblo';
    'id_cobrador_asignado',
    'activa',
    'fecha_asignacion',
];
protected $casts = [
        'activa' => 'boolean',
        'fecha_asignacion' => 'date',
    ];

    /**
     * Relación: Pertenece a un pueblo
     */
    public function pueblo()
    {
        return $this->belongsTo(Pueblo::class, 'id_pueblo', 'id_pueblo');
    }

    /**
     * Relación: Tiene un cobrador asignado
     */
    public function cobrador()
    {
        return $this->belongsTo(Usuario::class, 'id_cobrador_asignado', 'id_usuario');
    }

    /**
     * Relación: Una ruta tiene muchos deudores
     */
    public function deudores()
    {
        return $this->hasMany(Deudor::class, 'id_ruta', 'id_ruta');
    }

    /**
     * Scope para rutas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    /**
     * Scope para rutas asignadas a un cobrador específico
     */
    public function scopeDelCobrador($query, $idCobrador)
    {
        return $query->where('id_cobrador_asignado', $idCobrador);
    }

}