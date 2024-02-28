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
        Schema::create('razon_bajas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idBajaProcesada');
            $table->unsignedBigInteger('idRazon');
            $table->timestamps();
            $table->foreign('idBajaProcesada')->references('id')->on('baja_procesadas');
            $table->foreign('idRazon')->references('id')->on('razons');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('razon_bajas');
    }
};
