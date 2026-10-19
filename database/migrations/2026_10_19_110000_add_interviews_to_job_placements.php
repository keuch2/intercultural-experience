<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Job Placement: entrevistas (sponsor y empleador) y fecha de aceptación laboral.
 * La fecha de elección de la oferta sigue en acceptance_date (solo cambia la etiqueta).
 * Courier / tracking / recepción del DS y T&C dejan de usarse: las columnas quedan
 * para no perder datos y el estado 'ds_shipped' pasa a 'documents_complete'.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_placements', function (Blueprint $table) {
            $table->date('sponsor_interview_date')->nullable()->after('acceptance_date');
            $table->string('sponsor_interview_result', 20)->default('pending')->after('sponsor_interview_date');
            $table->date('employer_interview_date')->nullable()->after('sponsor_interview_result');
            $table->string('employer_interview_result', 20)->default('pending')->after('employer_interview_date');
            $table->date('job_accepted_at')->nullable()->after('employer_interview_result');
        });

        DB::statement("ALTER TABLE job_placements MODIFY status ENUM('pending','in_progress','job_accepted','documents_complete','ds_shipped','completed','cancelled') NOT NULL DEFAULT 'pending'");
        DB::table('job_placements')->where('status', 'ds_shipped')->update(['status' => 'documents_complete']);

        DB::statement("ALTER TABLE job_pool_assignments MODIFY status ENUM('active','released','reassigned','changed','job_cancelled') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        DB::table('job_pool_assignments')->whereIn('status', ['changed', 'job_cancelled'])->update(['status' => 'released']);
        DB::statement("ALTER TABLE job_pool_assignments MODIFY status ENUM('active','released','reassigned') NOT NULL DEFAULT 'active'");

        DB::table('job_placements')->where('status', 'job_accepted')->update(['status' => 'in_progress']);
        DB::statement("ALTER TABLE job_placements MODIFY status ENUM('pending','in_progress','documents_complete','ds_shipped','completed','cancelled') NOT NULL DEFAULT 'pending'");

        Schema::table('job_placements', function (Blueprint $table) {
            $table->dropColumn(['sponsor_interview_date', 'sponsor_interview_result', 'employer_interview_date', 'employer_interview_result', 'job_accepted_at']);
        });
    }
};
