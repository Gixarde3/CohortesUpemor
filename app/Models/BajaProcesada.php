<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BajaProcesada extends Model
{
    use HasFactory;
    protected $fillable = [
        "idBaja",
        "idAlumno",
        "bajaDefinitiva",
        "motivo",
        "periodo"
    ];
}
