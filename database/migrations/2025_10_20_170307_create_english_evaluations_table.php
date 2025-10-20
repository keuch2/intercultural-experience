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
        Schema::create('english_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('ef_set_id')->nullable()->comment('ID del test en EF SET');
            $table->enum('cefr_level', ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'])->comment('Nivel CEFR obtenido');
            $table->enum('classification', ['INSUFFICIENT', 'GOOD', 'GREAT', 'EXCELLENT'])->comment('Clasificación automática');
            $table->integer('score')->comment('Puntaje obtenido (0-100)');
            $table->integer('attempt_number')->default(1)->comment('Número de intento (máx 3)');
            $table->timestamp('evaluated_at')->comment('Fecha de evaluación');
            $table->text('notes')->nullable()->comment('Notas adicionales');
            $table->timestamps();
            
            // Índices
            $table->index('user_id');
            $table->index('cefr_level');
            $table->index('attempt_number');
            
            // Constraint: máximo 3 intentos por usuario
            $table->unique(['user_id', 'attempt_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('english_evaluations');
    }
};
