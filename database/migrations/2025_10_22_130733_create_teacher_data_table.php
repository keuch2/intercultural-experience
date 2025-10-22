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
        Schema::create('teacher_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained()->onDelete('cascade');
            
            // Formación Académica (OBLIGATORIO)
            $table->string('university_degree')->nullable(); // Título universitario
            $table->string('degree_title')->nullable(); // Lic. en Educación, Prof. en...
            $table->string('educational_institution')->nullable();
            $table->integer('graduation_year')->nullable();
            $table->string('degree_certificate_path')->nullable(); // Apostillado
            $table->string('academic_transcript_path')->nullable(); // Certificado estudios apostillado
            
            // Registro MEC (OBLIGATORIO)
            $table->string('mec_registration_number')->nullable();
            $table->string('mec_certificate_path')->nullable();
            $table->date('mec_registration_date')->nullable();
            $table->boolean('mec_validated')->default(false);
            $table->date('mec_validation_date')->nullable();
            
            // Especializaciones / Diplomados
            $table->json('specializations')->nullable(); // Array de especializaciones
            
            // Experiencia Docente (CRÍTICO)
            $table->text('teaching_experience_detailed')->nullable(); // Experiencia detallada
            $table->json('institutions_worked')->nullable(); // Array de instituciones
            $table->text('age_segments')->nullable(); // inicial, primaria, secundaria
            $table->text('subjects_taught')->nullable(); // Materias impartidas
            $table->integer('weekly_hours')->nullable(); // Carga horaria semanal
            $table->integer('years_experience')->nullable(); // Total años experiencia
            $table->enum('institution_type', ['public', 'private', 'both'])->nullable();
            $table->text('educational_levels')->nullable(); // Niveles donde trabajó
            $table->text('methodologies')->nullable(); // Metodologías que maneja
            
            // Cartas de Referencia Profesional (Mínimo 2)
            $table->json('professional_references')->nullable(); // Array de referencias
            
            // Evaluación de Inglés (MUY ESTRICTO)
            $table->enum('english_level_required', ['advanced', 'c1_minimum'])->default('c1_minimum');
            $table->enum('cefr_level', ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'])->nullable();
            $table->string('efset_id')->nullable();
            $table->integer('english_attempts')->default(0); // Máximo 3
            $table->date('last_english_evaluation')->nullable();
            $table->date('english_deadline')->nullable(); // 30.07
            $table->boolean('english_requirement_met')->default(false);
            
            // Expectativas y Preparación
            $table->text('program_expectations')->nullable();
            $table->boolean('tolerant_to_demands')->nullable();
            $table->boolean('flexible_cultural_activities')->nullable();
            $table->boolean('share_paraguayan_culture')->nullable();
            $table->boolean('intention_to_stay')->default(false);
            $table->boolean('willing_share_accommodation')->nullable();
            $table->boolean('aware_maturity_required')->nullable();
            
            // Posición Laboral (Post-matching)
            $table->unsignedBigInteger('school_district_id')->nullable();
            $table->string('specific_school')->nullable();
            $table->string('school_city')->nullable();
            $table->string('school_state')->nullable();
            $table->enum('education_level', ['elementary', 'middle', 'high_school'])->nullable();
            $table->text('subjects_to_teach')->nullable();
            $table->date('teaching_start_date')->nullable(); // Típicamente agosto
            $table->date('teaching_end_date')->nullable(); // Típicamente mayo/junio
            $table->decimal('annual_salary', 10, 2)->nullable();
            $table->text('housing_notes')->nullable(); // No incluido, a cargo del participante
            
            // Job Fair
            $table->boolean('participated_job_fair')->default(false);
            $table->json('job_fair_interviews')->nullable();
            $table->date('job_offer_received_at')->nullable();
            
            // Etapa Actual del Proceso
            $table->enum('current_stage', [
                'registration',
                'english_evaluation',
                'documentation',
                'mec_validation',
                'application_review',
                'job_fair',
                'district_interviews',
                'job_offer',
                'position_confirmed',
                'visa_process',
                'pre_departure',
                'in_program',
                'completed'
            ])->default('registration');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('application_id');
            $table->index('current_stage');
            $table->index('mec_registration_number');
            $table->index('mec_validated');
            $table->index('english_requirement_met');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_data');
    }
};
