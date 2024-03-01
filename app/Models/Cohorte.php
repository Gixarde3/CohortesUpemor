<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cohorte extends Model
{
    use HasFactory;
    protected $fillable = [
        'periodo',
        'anio',
        'plan',
        'idCreador'
    ];
    public function alumnos(){
        return $this->hasMany(Alumno::class);
    }
}
