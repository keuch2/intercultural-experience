<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Unidad de programs.duration (days|weeks|months|years). Los valores existentes eran días.
     */
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->string('duration_unit', 10)->default('days')->after('duration');
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn('duration_unit');
        });
    }
};
