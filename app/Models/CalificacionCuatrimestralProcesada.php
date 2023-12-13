<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalificacionCuatrimestralProcesada extends Model
{
    use HasFactory;
    public function calificacionesCuatrimestrales(){
        return $this->belongsTo(CalificacionCuatrimestral::class);
    }
}
