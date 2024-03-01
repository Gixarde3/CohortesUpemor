<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Model
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */

   
    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::make($password);
    }

    public function grupos(){
        return $this->hasMany(Grupo::class);
    }
    public function admisiones(){
        return $this->hasMany(Admision::class);
    }
    public function cohortes(){
        return $this->hasMany(Cohorte::class);
    }
    public function calificacions(){
        return $this->hasMany(Calificacion::class);
    }
}
