<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CalificacionProcesada; // Import the missing class

class Alumno extends Model
{
    use HasFactory;
    protected $fillable = ['matricula', 'apP', 'apM', 'nombre'];
    public function calificacionProcesadas(){
        return $this->hasMany(CalificacionProcesada::class);
    }
}
