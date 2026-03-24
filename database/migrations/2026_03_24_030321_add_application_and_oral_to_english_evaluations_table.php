<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Módulo 3: Add application_id for grouping evaluations by program/year.
 * Módulo 8: Add oral_score field for Au Pair evaluations.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('english_evaluations', function (Blueprint $table) {
            if (!Schema::hasColumn('english_evaluations', 'application_id')) {
                $table->foreignId('application_id')->nullable()->after('user_id')
                      ->constrained('applications')->onDelete('set null');
            }
            if (!Schema::hasColumn('english_evaluations', 'evaluated_by')) {
                $table->string('evaluated_by')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('english_evaluations', 'oral_score')) {
                $table->string('oral_score')->nullable()->after('score')
                      ->comment('Oral evaluation: Good, Great, or Excellent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('english_evaluations', function (Blueprint $table) {
            if (Schema::hasColumn('english_evaluations', 'application_id')) {
                $table->dropForeign(['application_id']);
                $table->dropColumn('application_id');
            }
            if (Schema::hasColumn('english_evaluations', 'evaluated_by')) {
                $table->dropColumn('evaluated_by');
            }
            if (Schema::hasColumn('english_evaluations', 'oral_score')) {
                $table->dropColumn('oral_score');
            }
        });
    }
};
