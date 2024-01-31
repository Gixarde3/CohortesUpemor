<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            'foto' => '657a3e3b4058d.png'
        ]);
    }
}
