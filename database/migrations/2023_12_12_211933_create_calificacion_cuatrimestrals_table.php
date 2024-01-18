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
        Schema::create('calificacion_cuatrimestrals', function (Blueprint $table) {
            $table->id();
            $table->string('periodo');
            $table->year('anio');
            $table->string('carrera');
            $table->string('programaEducativo');
            $table->unsignedBigInteger('idArchivo');
            $table->unsignedBigInteger('idCreador');
            $table->foreign('idArchivo')->references('id')->on('excels')->onDelete('cascade');
            $table->foreign('idCreador')->references('id')->on('usuarios')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calificacion_cuatrimestrals');
    }
};
