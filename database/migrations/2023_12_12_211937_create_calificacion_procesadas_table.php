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
        Schema::create('calificacion_procesadas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idCohorte');
            $table->unsignedBigInteger('idGrupo');
            $table->unsignedBigInteger('idMateria');
            $table->unsignedBigInteger('idProfesor');
            $table->unsignedBigInteger('idAlumno');
            $table->integer('Calificacion')->nullable();
            $table->string('TipoCursamiento')->nullable();
            $table->foreign('idCohorte')->references('id')->on('cohortes')->onDelete('cascade');
            $table->foreign('idGrupo')->references('id')->on('grupos')->onDelete('cascade');
            $table->foreign('idMateria')->references('id')->on('materias')->onDelete('cascade');
            $table->foreign('idProfesor')->references('id')->on('profesors')->onDelete('cascade');
            $table->foreign('idAlumno')->references('id')->on('alumnos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calificacion_procesadas');
    }
};
