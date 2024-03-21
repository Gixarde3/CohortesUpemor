<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        Validator::extend('unique_concat', function ($attribute, $value, $parameters, $validator) {
            // Verificamos si el número de parámetros es válido para la concatenación
            if (count($parameters) < 2) {
                throw new \InvalidArgumentException('Se requieren al menos dos parámetros para la concatenación.');
            }
        
            // El primer parámetro es el nombre de la tabla
            $table = array_shift($parameters);
        
            // Construimos la cláusula WHERE para la concatenación de campos
            $concatFields = implode(", '|', ", $parameters);
        
            // Concatenamos los valores de entrada para la comparación
            $concatValues = implode('|', $value);
        
            // Ejecutamos la consulta para verificar si la concatenación existe en la base de datos
            $count = DB::table($table)
                ->whereRaw("CONCAT($concatFields) = ?", [$concatValues])
                ->count();
        
            return $count === 0;
        });
    }
}
