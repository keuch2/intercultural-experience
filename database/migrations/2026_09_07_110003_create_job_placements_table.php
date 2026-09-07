<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Job Placement: la oferta finalmente asignada + documentación por Sponsor y datos
 * SEVIS / DS-2019. Los documentos del Sponsor son requisitos del motor en la etapa
 * `placement` (uploaded_by=staff).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_placements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_process_id')->unique()->constrained('program_processes')->cascadeOnDelete();
            $table->foreignId('job_pool_assignment_id')->nullable()->constrained('job_pool_assignments')->nullOnDelete();
            $table->foreignId('sponsor_id')->nullable()->constrained('sponsors')->nullOnDelete();
            $table->enum('status', ['pending', 'in_progress', 'documents_complete', 'ds_shipped', 'completed', 'cancelled'])->default('pending');
            $table->date('acceptance_date')->nullable();
            $table->date('program_start_date')->nullable();
            $table->date('program_end_date')->nullable();
            $table->dateTime('terms_accepted_at')->nullable();
            $table->string('sevis_number', 30)->nullable();
            $table->string('ds2019_number', 30)->nullable();
            $table->string('ds_tracking_carrier', 50)->nullable();
            $table->string('ds_tracking_number', 80)->nullable();
            $table->date('ds_received_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_placements');
    }
};
