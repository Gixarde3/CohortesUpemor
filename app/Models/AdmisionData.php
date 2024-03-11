<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmisionData extends Model
{
    use HasFactory;
    protected $fillable = [
        'idAdmision',
        'carrera',
        'solicitudes',
        'examenes_presentados'
    ];
}
