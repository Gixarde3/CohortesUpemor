<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cohorte extends Model
{
    use HasFactory;
    protected $fillable = [

    ];

    public function usuarios(){
        return $this->belongsTo(Usuario::class);
    }
    public function calificacionProcesadas(){
        return $this->hasMany(CalificacionProcesada::class);
    }
    public function bajas(){
        return $this->hasMany(Baja::class);
    }
}
