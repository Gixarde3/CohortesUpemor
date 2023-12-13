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
            $table->foreign('idCalificacionCuatrimestral')->references('id')->on('calificacion_cuatrimestrals')->onCascade('delete');
            $table->string('matricula');
            $table->string('claveMateria');
            $table->float('calificacion');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calificacion_cuatrimestral_procesadas');
    }
};
