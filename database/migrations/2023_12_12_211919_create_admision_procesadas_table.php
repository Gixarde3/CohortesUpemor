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
        Schema::create('admision_procesadas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idAdmision');
            $table->foreign('idAdmision')->references('id')->on('admisions')->onCascade('delete');
            $table->integer('alumnosCursoSeleccion');
            $table->integer('alumnosCursoSeleccionAprobados');
            $table->integer('alumnosInscritosPrimero');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admision_procesadas');
    }
};
