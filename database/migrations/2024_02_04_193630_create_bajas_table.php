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
        Schema::create('bajas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idUsuario');
            $table->string('archivo');
            $table->boolean('procesado')->default(false);
            $table->unsignedBigInteger('idCohorte');
            $table->foreign('idUsuario')->references('id')->on('usuarios');
            $table->foreign('idCohorte')->references('id')->on('cohortes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bajas');
    }
};
