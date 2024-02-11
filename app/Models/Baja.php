<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cohorte; // Add this import statement

class Baja extends Model
{
    use HasFactory;

    protected $fillable = [
        "idBaja",
        "idAlumno",
        "fecha",
        "motivo"
    ];
    public function bajasProcesadas(){
        return $this->hasMany(BajaProcesada::class);
    }
    public function cohortes(){
        return $this->belongsTo(Cohorte::class);
    }
    
}
