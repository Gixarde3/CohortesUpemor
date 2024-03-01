<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calificacion extends Model
{
    use HasFactory;

    public function usuarios()
    {
        return $this->belongsTo(Usuario::class, 'idCreador');
    }
    public function calificacionProcesadas()
    {
        return $this->hasMany(CalificacionProcesada::class);
    }
}
