<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admision extends Model
{
    use HasFactory;
    protected $fillable = [
        'archivo',
        'procesado',
        'periodo',
        'idCreador'
    ];
    public function usuarios(){
        return $this->belongsTo(Usuario::class);
    }
    public function aspirantes(){
        return $this->hasMany(Aspirante::class);
    }
}
