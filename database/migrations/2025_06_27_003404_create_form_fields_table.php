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
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_form_id')->constrained()->onDelete('cascade');
            $table->string('section_name')->nullable(); // Sección a la que pertenece
            $table->string('field_key'); // Clave única del campo
            $table->string('field_label'); // Etiqueta visible
            $table->enum('field_type', [
                'text', 'email', 'tel', 'number', 'date', 'datetime', 
                'textarea', 'select', 'radio', 'checkbox', 'file', 
                'signature', 'boolean', 'address', 'country', 'currency'
            ]);
            $table->text('description')->nullable();
            $table->text('placeholder')->nullable();
            $table->json('options')->nullable(); // Para select, radio, checkbox
            $table->json('validation_rules')->nullable(); // Reglas de validación
            $table->boolean('is_required')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('conditional_logic')->nullable(); // Lógica condicional
            $table->timestamps();
            
            $table->index(['program_form_id', 'section_name']);
            $table->index(['program_form_id', 'sort_order']);
            $table->unique(['program_form_id', 'field_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
