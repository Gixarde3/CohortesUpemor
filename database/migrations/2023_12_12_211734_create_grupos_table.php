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
        Schema::create('grupos', function (Blueprint $table) {
            $table->id();
            $table->string('grupo',1);
            $table->integer('grado');
            $table->string('periodo');
            $table->date('fecha');
            $table->unsignedBigInteger('idCreador');
            $table->unsignedBigInteger('idCohorte');
            $table->foreign('idCreador')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('idCohorte')->references('id')->on('cohortes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupos');
    }
};
