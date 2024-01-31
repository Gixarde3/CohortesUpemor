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
        Schema::create('cohortes', function (Blueprint $table) {
            $table->id();
            $table->string('periodo');
            $table->year('anio');
            $table->string('plan');
            $table->string('archivo')->nullable();
            $table->boolean('procesado')->default(false);
            $table->unsignedBigInteger('idCreador');
            $table->foreign('idCreador')->references('id')->on('usuarios')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cohortes');
    }
};
