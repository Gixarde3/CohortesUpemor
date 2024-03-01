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
        Schema::create('cenevals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idAspirante')->constrained('aspirantes');
            $table->boolean('pagado');
            $table->string('folio');
            $table->date('fecha');
            $table->integer('calificacion');
            $table->boolean('estado');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cenevals');
    }
};
