<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalificacionProcesada extends Model
{
    use HasFactory;
    protected $fillable = [
        'idCohorte',
        'idAlumno',
        'idMateria',
        'idProfesor',
        'idGrupo',
        'calificacion',
        'tipoCursamiento'
    ];
    public function alumnos(){
        return $this->belongsTo(Alumno::class);
    }
    public function materias(){
        return $this->belongsTo(Materia::class);
    }
    public function cohortes(){
        return $this->belongsTo(Cohorte::class);
    }
    public function profesores(){
        return $this->belongsTo(Profesor::class);
    }
    public function grupos(){
        return $this->belongsTo(Grupo::class);
    }
}
