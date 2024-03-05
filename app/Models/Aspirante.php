<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aspirante extends Model
{
    use HasFactory;
    protected $fillable = [
        'idAdmision',
        'idCurso',
        'cedula',
        'apP',
        'apM',
        'nombre',
        'email',
        'fecha_registro',
        'telefono1',
        'telefono2',
        'telefono3',
        'carrera',
        'municipio',
        'foraneo',
        'tipo',
        'promedio',
        'escuela_procedencia',
        'curp',
        'pago_curso',
        'aprobo_curso'
    ];
    public function admisiones(){
        return $this->belongsTo(Admision::class);
    }
    public function cursos(){
        return $this->belongsTo(Curso::class);
    } 
    public function cenevals(){
        return $this->hasMany(Ceneval::class);
    }
}
