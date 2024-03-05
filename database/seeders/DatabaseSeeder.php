<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Baja;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        \App\Models\Usuario::factory()->create([
            'noEmp' => '123',
            'nombre' => 'Marco Antonio',
            'apP' => 'Chavez',
            'apM' => 'Rodriguez',
            'tipoUsuario' => 3,
            'email' => 'antoniochavezmarco@gmail.com',
            'password' => 'Und3rt4le!',
            'foto' => '657a3e3b4058d.png',
            'token' => 'hNKC93UNPX8nE3lhYAZQgU3IoBmLed7ilZw1lLx7HTVrlmYsh2XN95IbPXEP'
        ]);

        \App\Models\Cohorte::factory()->create([
            'periodo' => 'I',
            'anio' => 2024,
            'plan' => 'ITI H2024',
            'idCreador' => 1
        ]);

        \App\Models\Calificacion::factory()->create([
            'archivo' => '65a80b5f55e66.xlsx',
            'idCreador' => 1,
            'carrera' => 'ITI',
            'periodo' => 'I',
            'anio' => 2024
        ]);
        \App\Models\Baja::factory()->create([
            'idUsuario' => 1,
            'archivo' => '65bfefb91dbd0.xlsx',
            'procesado' => 0,
            'periodo'=> 'I2024',
        ]);

        \App\Models\Admision::factory()->create([
            'archivo' => '65e20414767ce.xlsx',
            'procesado' => 0,
            'periodo' => 'I',
            'anio' => 2024,
            'idCreador' => 1
        ]);
        
    }
}
