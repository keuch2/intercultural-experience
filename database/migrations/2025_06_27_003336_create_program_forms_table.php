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
        Schema::create('program_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('version', 10)->default('1.0');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_published')->default(false);
            $table->json('settings')->nullable(); // Configuraciones generales del formulario
            $table->json('sections')->nullable(); // Secciones del formulario
            $table->text('terms_and_conditions')->nullable();
            $table->boolean('requires_signature')->default(false);
            $table->boolean('requires_parent_signature')->default(false); // Para menores de edad
            $table->integer('min_age')->nullable();
            $table->integer('max_age')->nullable();
            $table->timestamps();
            
            $table->index(['program_id', 'is_active']);
            $table->index(['program_id', 'is_published']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_forms');
    }
};
