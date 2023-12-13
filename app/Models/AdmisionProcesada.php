<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmisionProcesada extends Model
{
    use HasFactory;
    public function admisiones(){
        return $this->belongsTo(Admision::class);
    }
}
