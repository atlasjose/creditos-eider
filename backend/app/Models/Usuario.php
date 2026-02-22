<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class Usuario extends Authenticable{
    use HasApiTokens,HasFactory,Notifiable;

    protected $table='usarios';
    protected $primaryKey = 'id_usurio';

    protected $fillable=[
        'cedula',
        'nombres',
        'apellidos',
        'telefono',
        'email',
        'clave_hash',
        'id_rol',
        'id_jefe_asociado',
        'porcentaje_comision',
        'activo',
        'fecha_creacion',
    ];
    protected $casts =[
        'activo'=> 'boolean',
        'porcentaje_comision'=> 'decimal:2',
        'fecha_creacion'=>'datetime',
    ];
    /**
     * hash automatico de password
     * 
     */
    public function setClaveHashAttribute($value){
        $this->attribute['clave_hash']=Hash::make($value);
    }

    /**
     * Nombre completo del usuario 
     */

    public function setClaveHashAttribute($value){
        $this->attributes['clave_hash']= Hash::make($value);
    }

    /**
     *Nombre completo del usuario  
     */

    public function rol(){
        return $this->belongsTo(Role::class,'id_rol','id_rol');
    }

    /**
     *Tiene un jefe asociado 
     */

    public function jefe(){
        return $this->belongsTo(Usuario::class,'id_jefe_asociado','id_usuario');
    }

    /**
     *relacion:tiene muchos subordiandos si es jefe  
     */

    public function subordinados(){
        return $this->hasMany(Usuario::class,'id_jefe_asociado','id_usuario');
    }
    /**
     *relacion:puede tener rutas asigandas si es un cobrador   
     */

    public function rutasAsignadas(){
        return $this->hasMany(RutaCobro::class,'id_cobrador_asociado','id_usuario');
    }
    /**
     * Relación: Puede haber creado deudores (si es jefe)
     */
    public function deudoresCreados()
    {
        return $this->hasMany(Deudor::class, 'id_creado_por', 'id_usuario');
    }

    /**
     * Relación: Puede tener ventas (si es vendedor)
     */
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'id_vendedor', 'id_usuario');
    }

    /**
     * Relación: Puede tener pagos (si es cobrador)
     */
    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_cobrador', 'id_usuario');
    }

    /**
     * Relación: Puede tener comisiones (si es cobrador)
     */
    public function comisiones()
    {
        return $this->hasMany(ComisionCobrador::class, 'id_cobrador', 'id_usuario');
    }

    /**
     * Verificar si el usuario es Jefe
     */
    public function isJefe()
    {
        return $this->rol && $this->rol->nombre_rol === 'JEFE';
    }

    /**
     * Verificar si el usuario es Cobrador
     */
    public function isCobrador()
    {
        return $this->rol && $this->rol->nombre_rol === 'COBRADOR';
    }

    /**
     * Verificar si el usuario es Vendedor
     */
    public function isVendedor()
    {
        return $this->rol && $this->rol->nombre_rol === 'VENDEDOR';
    }

    /**
     * Override de getAuthPassword para Sanctum
     */
    public function getAuthPassword()
    {
        return $this->clave_hash;
    }
}
    


    




}
