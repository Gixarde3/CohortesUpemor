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
            $table->foreignId('idAdmision')->constrained('admisions');
            $table->unsignedBigInteger('idCurso')->nullable();
            $table->integer('cedula');
            $table->date('fecha_registro');
            $table->string('apP');
            $table->string('apM');
            $table->string('nombre');
            $table->string('email')->nullable();
            $table->string('telefono1',10)->nullable();
            $table->string('telefono2',10)->nullable();
            $table->string('telefono3',10)->nullable();
            $table->string('carrera');
            $table->string('municipio')->nullable();
            $table->boolean('foraneo')->default(false);
            $table->string('tipo');
            $table->float('promedio');
            $table->string('escuela_procedencia');
            $table->string('curp', 18);
            $table->boolean('pagoCurso')->nullable();
            $table->boolean('aproboCurso')->nullable();
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
