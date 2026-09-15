<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_pool_offers', function (Blueprint $table) {
            // Puesto laboral (obligatorio desde el admin; nullable para ofertas ya cargadas)
            $table->string('job_title', 150)->nullable()->after('program_id');
            // Fecha límite para postular: pasada esa fecha la oferta deja de mostrarse en la app
            $table->date('application_deadline')->nullable()->after('positions_available');
        });
    }

    public function down(): void
    {
        Schema::table('job_pool_offers', function (Blueprint $table) {
            $table->dropColumn(['job_title', 'application_deadline']);
        });
    }
};
