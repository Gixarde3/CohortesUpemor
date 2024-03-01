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
            $table->string('periodo');
            $table->text('observaciones')->nullable();
            $table->foreign('idBaja')->references('id')->on('bajas')->onDelete('cascade');
            $table->foreign('idAlumno')->references('id')->on('alumnos')->onDelete('cascade');
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
