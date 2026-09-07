<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Los programas del motor definen sus propias claves de etapa desde el admin;
 * un enum fijo en applications.current_stage las rechaza. Se amplía a string.
 * (Widening: todos los valores existentes del enum siguen siendo válidos.)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('current_stage', 50)->nullable()->default('registration')->change();
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->enum('current_stage', ['registration', 'documentation', 'evaluation', 'in_review', 'approved', 'in_program', 'completed', 'withdrawn'])
                ->nullable()->default('registration')->change();
        });
    }
};
