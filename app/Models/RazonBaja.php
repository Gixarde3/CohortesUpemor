<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RazonBaja extends Model
{
    use HasFactory;
    protected $fillable = ['idBajaProcesada', 'idRazon'];
    public function bajas_procesadas()
    {
        return $this->belongsTo(BajaProcesada::class);
    }   
    public function razons()
    {
        return $this->belongsTo(Razon::class);
    }
}
