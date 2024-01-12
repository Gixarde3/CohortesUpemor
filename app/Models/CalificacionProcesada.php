<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalificacionProcesada extends Model
{
    use HasFactory;
    protected $fillable = [
        'idCalificacionCuatrimestral',
        'ClaveGrupo',
        'NombreGrupo',
        'LetraGrupo',
        'PaternoProfesor',
        'MaternoProfesor',
        'NombreProfesor',
        'ClaveMateria',
        'NombreMateria',
        'PlanEstudios',
        'Matricula',
        'PaternoAlumno',
        'MaternoAlumno',
        'NombreAlumno',
        'EstadoAlumno',
        'CalificacionAlumno',
        'TipoCursamiento'
    ];

    public function calificacionesCuatrimestrales(){
        return $this->belongsTo(CalificacionCuatrimestral::class);
    }
}
