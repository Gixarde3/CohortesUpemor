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
        Schema::create('admision_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idAdmision')->constrained('admisions')->onDelete('cascade');
            $table->string('carrera');
            $table->integer('lugares_ofertados');
            $table->integer('solicitudes');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admision_data');
    }
};
