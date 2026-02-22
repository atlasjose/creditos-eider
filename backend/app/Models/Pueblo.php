<?php
namespace App\Models;
use Illuminate\Datebase\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pueblo extends Model{
    use HasFactory;
    protected $table = 'pueblos';
    protected $primatyKey ='id_pueblo';

    protected $fillable=[
        'nombre_pueblo',
        'departamento',
        'latitud',
        'longitud',
        'activo',
        
    ];
    protected $casts=[
        'activo'=> 'boolean',
        'latitud'=>'decimal:6',
        'longitud'=>'decimal:6',
    ];
    /**
     * relacion:de un pueblo con muchas rutas 
     */

    public function rutas(){
        return $this->hasMany(RutaCobro::class,'id_publo','id_publo');

        /**
         * scope para pueblos activos 
         */

        public function scopeActivos($query){
            return $quety->where('activo',true);
        }
    }


}