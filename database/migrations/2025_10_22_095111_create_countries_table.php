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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique(); // United States, Canada, etc.
            $table->string('code', 3)->unique(); // USA, CAN, GBR (ISO 3166-1 alpha-3)
            $table->string('iso2', 2)->nullable(); // US, CA, GB (ISO 3166-1 alpha-2)
            $table->string('region', 50)->nullable(); // North America, Europe, Asia, etc.
            $table->string('flag_emoji', 10)->nullable(); // 🇺🇸 🇨🇦 🇬🇧
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0); // Para ordenar en listas
            $table->timestamps();
            
            // Índices para búsquedas rápidas
            $table->index('code');
            $table->index('region');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
