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
        Schema::create('aspirantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idAdmision')->constrained('admisions')->onDelete('cascade');
            $table->unsignedBigInteger('idCurso')->nullable();
            $table->integer('cedula')->nullable();
            $table->string('apP')->nullable();
            $table->string('apM')->nullable();
            $table->string('nombre')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono1')->nullable();
            $table->string('telefono2')->nullable();
            $table->string('telefono3')->nullable();
            $table->string('carrera')->nullable();
            $table->string('municipio')->nullable();
            $table->boolean('foraneo')->default(false);
            $table->string('tipo')->nullable();
            $table->dateTime('fecha_registro')->nullable();
            $table->float('promedio')->nullable();
            $table->string('escuela_procedencia')->nullable();
            $table->string('curp', 18)->nullable();
            $table->boolean('pago_curso')->nullable();
            $table->boolean('aprobo_curso')->nullable();
            $table->foreign('idCurso')->references('id')->on('cursos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aspirantes');
    }
};
