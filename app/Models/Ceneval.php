<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ceneval extends Model
{
    use HasFactory;
    protected $fillable = [
        'idAspirante',
        'pagado',
        'folio',
        'calificacion',
        'fecha',
        'estado'
    ];
    public function aspirantes(){
        return $this->belongsTo(Aspirante::class);
    }
    
}
