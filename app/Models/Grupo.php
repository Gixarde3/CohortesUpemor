<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    use HasFactory;
    protected $fillable = ['clave', 'nombre', 'letra', 'idCohorte', 'grado'];
    public function calificacionProcesadas(){
        return $this->hasMany(CalificacionProcesada::class);
    }
}
