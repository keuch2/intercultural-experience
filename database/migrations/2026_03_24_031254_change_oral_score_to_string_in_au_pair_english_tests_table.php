<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Módulo 8: Change oral_score from integer to string to support
 * Good/Great/Excellent selector for Au Pair evaluations.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('au_pair_english_tests', function (Blueprint $table) {
            $table->string('oral_score', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('au_pair_english_tests', function (Blueprint $table) {
            $table->unsignedSmallInteger('oral_score')->nullable()->change();
        });
    }
};
