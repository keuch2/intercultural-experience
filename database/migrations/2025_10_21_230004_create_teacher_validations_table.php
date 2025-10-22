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
        Schema::create('teacher_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('application_id')->nullable()->constrained()->onDelete('cascade');
            
            // Validación MEC (Ministerio de Educación y Ciencias)
            $table->string('mec_registration_number')->nullable();
            $table->boolean('has_mec_validation')->default(false);
            $table->date('mec_validation_date')->nullable();
            $table->string('mec_certificate_path')->nullable();
            $table->enum('mec_status', ['pending', 'approved', 'rejected', 'expired'])->default('pending');
            
            // Información académica
            $table->string('teaching_degree_title');
            $table->string('university_name');
            $table->year('graduation_year');
            $table->string('diploma_path')->nullable();
            $table->boolean('diploma_apostilled')->default(false);
            $table->string('transcript_path')->nullable();
            $table->boolean('transcript_apostilled')->default(false);
            
            // Experiencia docente
            $table->integer('teaching_years_total');
            $table->integer('teaching_years_verified');
            $table->json('subjects_taught')->nullable(); // Array de materias
            $table->json('grade_levels_taught')->nullable(); // Array de niveles
            $table->enum('current_employment_status', ['employed', 'unemployed', 'on_leave']);
            $table->string('current_school_name')->nullable();
            $table->string('current_school_contact')->nullable();
            
            // Certificaciones adicionales
            $table->boolean('has_tefl_certification')->default(false);
            $table->string('tefl_certificate_path')->nullable();
            $table->boolean('has_tesol_certification')->default(false);
            $table->string('tesol_certificate_path')->nullable();
            $table->json('other_certifications')->nullable();
            
            // Antecedentes
            $table->boolean('has_criminal_record')->default(false);
            $table->string('criminal_record_path')->nullable();
            $table->date('criminal_record_date')->nullable();
            $table->boolean('has_child_abuse_clearance')->default(false);
            $table->string('child_abuse_clearance_path')->nullable();
            
            // Job Fair
            $table->boolean('registered_for_job_fair')->default(false);
            $table->date('job_fair_date')->nullable();
            $table->enum('job_fair_status', ['registered', 'attended', 'matched', 'not_matched', 'cancelled'])->nullable();
            $table->integer('interviews_scheduled')->default(0);
            $table->integer('offers_received')->default(0);
            
            // Preferencias de colocación
            $table->json('preferred_states')->nullable(); // Array de estados preferidos
            $table->json('preferred_grade_levels')->nullable();
            $table->json('preferred_subjects')->nullable();
            $table->enum('school_type_preference', ['public', 'private', 'charter', 'any'])->default('any');
            $table->boolean('willing_to_relocate')->default(true);
            
            // Estado de validación
            $table->enum('validation_status', ['incomplete', 'pending_review', 'approved', 'rejected'])->default('incomplete');
            $table->text('rejection_reasons')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users');
            $table->datetime('validation_completed_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['user_id', 'validation_status']);
            $table->index('mec_status');
            $table->index('job_fair_status');
            $table->index('has_mec_validation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_validations');
    }
};
