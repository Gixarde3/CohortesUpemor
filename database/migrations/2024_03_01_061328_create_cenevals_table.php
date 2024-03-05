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
            $table->foreignId('idAspirante')->constrained('aspirantes')->onDelete('cascade');
            $table->boolean('pagado')->nullable();
            $table->string('folio')->nullable();
            $table->integer('calificacion')->nullable();
            $table->date('fecha')->nullable();
            $table->boolean('estado')->nullable();
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
