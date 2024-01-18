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
            $table->unsignedBigInteger('idCalificacionCuatrimestral');
            $table->foreign('idCalificacionCuatrimestral')->references('id')->on('calificacion_cuatrimestrals')->onDelete('cascade');
            $table->string('ClaveGrupo')->nullable();
            $table->string('NombreGrupo')->nullable();
            $table->string('LetraGrupo')->nullable();
            $table->string('PaternoProfesor')->nullable();
            $table->string('MaternoProfesor')->nullable();
            $table->string('NombreProfesor')->nullable();
            $table->string('ClaveMateria')->nullable();
            $table->string('NombreMateria')->nullable();
            $table->string('PlanEstudios')->nullable();
            $table->string('Matricula');
            $table->string('PaternoAlumno')->nullable();
            $table->string('MaternoAlumno')->nullable();
            $table->string('NombreAlumno')->nullable();
            $table->string('EstadoAlumno')->nullable();
            $table->integer('CalificacionAlumno')->nullable();
            $table->string('TipoCursamiento')->nullable();
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
