<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profesor extends Model
{
    use HasFactory;
    protected $fillable = ['apP', 'apM', 'nombre'];
    public function calificacionProcesadas(){
        return $this->hasMany(CalificacionProcesada::class);
    }
}
