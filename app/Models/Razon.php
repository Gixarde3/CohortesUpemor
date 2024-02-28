<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Razon extends Model
{
    use HasFactory;
    protected $fillable = ['nombre'];
    public function razon_bajas()
    {
        return $this->hasMany(RazonBaja::class);
    }
}
