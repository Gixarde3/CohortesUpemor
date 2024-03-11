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
            $table->integer('tipoUsuario');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('foto');
            $table->string('recuperacion')->unique()->nullable();
            $table->boolean('activo')->default(true);
            $table->string('token')->unique()->nullable();
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
