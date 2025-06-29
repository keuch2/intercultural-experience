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
        Schema::create('form_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category'); // academic, volunteer, internship, language, cultural, etc.
            $table->string('icon')->nullable(); // CSS class para el icono
            $table->json('template_data'); // Estructura completa del formulario (secciones y campos)
            $table->json('default_settings')->nullable(); // Configuraciones por defecto
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0); // Contador de usos
            $table->timestamps();
            
            $table->index(['category', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_templates');
    }
};
