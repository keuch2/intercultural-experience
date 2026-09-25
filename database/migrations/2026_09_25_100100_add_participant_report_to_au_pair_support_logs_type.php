<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Nuevo tipo 'participant_report' (reporte enviado por la participante desde la app).
 * El rollback solo es seguro si no existen filas con ese tipo.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE au_pair_support_logs MODIFY log_type ENUM('arrival_followup','monthly_followup','incident','experience_evaluation','participant_report') NOT NULL DEFAULT 'monthly_followup'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::table('au_pair_support_logs')->where('log_type', 'participant_report')->update(['log_type' => 'incident']);
            DB::statement("ALTER TABLE au_pair_support_logs MODIFY log_type ENUM('arrival_followup','monthly_followup','incident','experience_evaluation') NOT NULL DEFAULT 'monthly_followup'");
        }
    }
};
