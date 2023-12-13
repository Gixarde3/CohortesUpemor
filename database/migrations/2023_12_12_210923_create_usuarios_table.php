<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->integer('noEmp')->unique();
            $table->string('nombre');
            $table->string('apP');
            $table->string('apM');
            $table->string('tipoUsuario');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('foto');
            $table->string('recuperacion')->unique()->nullable();
            $table->string('token')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
