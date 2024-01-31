<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    use HasFactory;
    protected $fillable = ['clave', 'nombre', 'plan'];
    public function calificacionProcesadas(){
        return $this->hasMany(CalificacionProcesada::class);
    }
}
