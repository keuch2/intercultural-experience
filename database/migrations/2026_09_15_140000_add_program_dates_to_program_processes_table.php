<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('program_processes', function (Blueprint $table) {
            // Fecha de inicio/fin del programa (viaje/trabajo), distinta de la inscripción
            $table->date('program_start_date')->nullable()->after('enrollment_date');
            $table->date('program_end_date')->nullable()->after('program_start_date');
        });
    }

    public function down(): void
    {
        Schema::table('program_processes', function (Blueprint $table) {
            $table->dropColumn(['program_start_date', 'program_end_date']);
        });
    }
};
