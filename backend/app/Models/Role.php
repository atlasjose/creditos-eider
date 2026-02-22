<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Role extends Model
{
    use HasFactory;
    protected $table='roles';
    protected $primaryKey='id_rol';
    protected $fillable =[
        'nombre_rol',
        'descripcion',
        'permisos',
    ];
    protected $casts = [
        'permisos'=>'array',
    ];

    /**
     * Relacion: un rol tiene muchos usuarios 
     */
    public funtion usuarios(){
        return $this->hasMany(Usuarios::class,'id_rol','id_rol');
    }
}