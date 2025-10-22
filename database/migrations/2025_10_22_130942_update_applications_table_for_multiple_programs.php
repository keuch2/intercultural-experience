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
        Schema::table('applications', function (Blueprint $table) {
            // Cambiar current_stage de texto libre a enum
            // Primero necesitamos eliminar la columna actual
            $table->dropColumn('current_stage');
        });
        
        Schema::table('applications', function (Blueprint $table) {
            // Agregar nueva columna current_stage como enum con valores por defecto
            $table->enum('current_stage', [
                'registration',
                'documentation',
                'evaluation',
                'in_review',
                'approved',
                'in_program',
                'completed',
                'withdrawn'
            ])->default('registration')->after('status');
            
            // Agregar campos para IE Cue (Alumni)
            $table->boolean('is_ie_cue')->default(false)->after('progress_percentage');
            $table->integer('programs_completed')->default(0)->after('is_ie_cue');
            $table->boolean('is_current_program')->default(true)->after('programs_completed');
            
            // Agregar índices
            $table->index('is_ie_cue');
            $table->index('is_current_program');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['is_ie_cue', 'programs_completed', 'is_current_program']);
            $table->dropColumn('current_stage');
        });
        
        Schema::table('applications', function (Blueprint $table) {
            $table->string('current_stage')->nullable();
        });
    }
};
