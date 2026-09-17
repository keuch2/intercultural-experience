<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('au_pair_processes', function (Blueprint $table) {
            // Marcas de la automatización por fechas (alertas enviadas / avance automático), idempotencia
            $table->json('automation_flags')->nullable()->after('program_end_date');
        });
    }

    public function down(): void
    {
        Schema::table('au_pair_processes', function (Blueprint $table) {
            $table->dropColumn('automation_flags');
        });
    }
};
