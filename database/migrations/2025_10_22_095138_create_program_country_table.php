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
        Schema::create('program_country', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->onDelete('cascade');
            $table->foreignId('country_id')->constrained()->onDelete('cascade');
            $table->boolean('is_primary')->default(false); // Marca el país principal del programa
            $table->integer('display_order')->default(0); // Orden de visualización
            $table->text('specific_locations')->nullable(); // Ej: "California, Texas, Florida"
            $table->timestamps();
            
            // Evitar duplicados: un programa no puede tener el mismo país dos veces
            $table->unique(['program_id', 'country_id']);
            
            // Índices para búsquedas
            $table->index('program_id');
            $table->index('country_id');
            $table->index('is_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_country');
    }
};
