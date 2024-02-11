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
        Schema::create('baja_procesadas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idBaja');
            $table->unsignedBigInteger('idAlumno');
            $table->boolean('bajaDefinitiva');
            $table->text('motivo');
            $table->foreign('idBaja')->references('id')->on('bajas');
            $table->foreign('idAlumno')->references('id')->on('alumnos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('baja_procesadas');
    }
};
