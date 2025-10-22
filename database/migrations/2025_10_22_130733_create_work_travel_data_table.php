<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('work_travel_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained()->onDelete('cascade');
            
            // Datos Académicos (OBLIGATORIOS)
            $table->string('university')->nullable();
            $table->string('career')->nullable();
            $table->string('year_semester')->nullable();
            $table->enum('modality', ['presencial', 'virtual', 'mixto'])->default('presencial');
            $table->string('university_certificate_path')->nullable(); // Constancia universitaria
            
            // Evaluación de Inglés
            $table->enum('english_level_self', ['basic', 'intermediate', 'advanced'])->nullable();
            $table->enum('english_level_certified', ['insufficient', 'good', 'great', 'excellent'])->nullable();
            $table->enum('cefr_level', ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'])->nullable();
            $table->string('efset_id')->nullable();
            $table->integer('english_attempts')->default(0); // Máximo 3
            $table->date('last_english_evaluation')->nullable();
            
            // Job Offer (Post-selección)
            $table->foreignId('job_offer_id')->nullable()->constrained('job_offers')->onDelete('set null');
            $table->enum('sponsor', ['AAG', 'AWA', 'GH', 'OTHER'])->nullable();
            $table->string('host_company_name')->nullable();
            $table->string('job_position')->nullable();
            $table->string('job_city')->nullable();
            $table->string('job_state')->nullable();
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->enum('housing', ['provided', 'assisted', 'not_provided'])->nullable();
            $table->date('program_start_date')->nullable();
            $table->date('program_end_date')->nullable();
            $table->date('job_reserved_at')->nullable();
            
            // Proceso de Entrevistas
            $table->enum('sponsor_interview_status', ['pending', 'scheduled', 'approved', 'rejected'])->default('pending');
            $table->dateTime('sponsor_interview_date')->nullable();
            $table->text('sponsor_interview_notes')->nullable();
            
            $table->enum('job_interview_status', ['pending', 'scheduled', 'approved', 'rejected'])->default('pending');
            $table->dateTime('job_interview_date')->nullable();
            $table->text('job_interview_notes')->nullable();
            
            // Expectativas y Actitud
            $table->text('program_expectations')->nullable();
            $table->boolean('tolerant_to_demands')->nullable();
            $table->boolean('flexible_to_changes')->nullable();
            $table->boolean('intention_to_stay')->default(false); // Advertencia
            $table->boolean('willing_share_accommodation')->nullable();
            $table->boolean('aware_adult_program')->nullable();
            
            // Etapa Actual del Proceso
            $table->enum('current_stage', [
                'registration',
                'english_evaluation',
                'documentation',
                'job_selection',
                'sponsor_interview',
                'job_interview',
                'job_confirmed',
                'visa_process',
                'pre_departure',
                'in_program',
                'completed'
            ])->default('registration');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('application_id');
            $table->index('current_stage');
            $table->index('sponsor_interview_status');
            $table->index('job_interview_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_travel_data');
    }
};
