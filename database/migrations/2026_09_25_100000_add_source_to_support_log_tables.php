<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['program_support_logs', 'au_pair_support_logs'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                // Quién originó el registro: staff (coordinador) o participant (desde la app)
                $t->string('source', 20)->default('staff')->after('logged_by');
            });
        }
    }

    public function down(): void
    {
        foreach (['program_support_logs', 'au_pair_support_logs'] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->dropColumn('source'));
        }
    }
};
