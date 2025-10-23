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
        Schema::create('intern_trainee_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Program Type
            $table->enum('program_type', ['intern', 'trainee'])->index();
            
            // Academic Info (for Interns)
            $table->string('university_name')->nullable();
            $table->string('degree_field')->nullable();
            $table->integer('current_year')->nullable();
            $table->integer('total_years')->nullable();
            $table->decimal('gpa', 3, 2)->nullable();
            $table->date('expected_graduation')->nullable();
            $table->date('graduation_date')->nullable(); // For recent graduates
            $table->boolean('is_currently_enrolled')->default(false);
            
            // Work Experience (for Trainees)
            $table->string('field_of_expertise')->nullable();
            $table->integer('years_of_experience')->nullable();
            $table->integer('years_verified')->nullable();
            $table->string('current_position')->nullable();
            $table->string('current_employer')->nullable();
            $table->json('previous_positions')->nullable(); // [{company, position, years, description}]
            
            // Training Plan Details
            $table->foreignId('host_company_id')->nullable()->constrained()->onDelete('set null');
            $table->string('training_plan_title')->nullable();
            $table->text('training_objectives')->nullable();
            $table->json('learning_goals')->nullable();
            $table->json('skills_to_develop')->nullable();
            $table->text('job_description')->nullable();
            $table->integer('hours_per_week')->nullable();
            
            // Industry & Sector
            $table->string('industry_sector')->nullable(); // IT, Engineering, Finance, etc.
            $table->json('specific_sectors')->nullable();
            $table->string('position_title')->nullable();
            
            // Duration
            $table->date('program_start_date')->nullable();
            $table->date('program_end_date')->nullable();
            $table->integer('duration_months')->nullable(); // 3-18 months
            $table->boolean('is_flexible_duration')->default(false);
            
            // Compensation (if any)
            $table->boolean('is_paid_position')->default(false);
            $table->decimal('stipend_amount', 10, 2)->nullable();
            $table->string('stipend_frequency')->nullable(); // monthly, weekly
            $table->boolean('provides_housing')->default(false);
            $table->text('housing_details')->nullable();
            
            // Requirements Validation
            $table->boolean('meets_age_requirement')->default(false);
            $table->boolean('meets_education_requirement')->default(false);
            $table->boolean('meets_experience_requirement')->default(false);
            $table->boolean('meets_english_requirement')->default(false);
            $table->boolean('has_valid_passport')->default(false);
            $table->boolean('has_clean_record')->default(false);
            
            // For Interns - must be student or recent grad
            $table->boolean('is_student_or_recent_grad')->default(false);
            $table->integer('months_since_graduation')->nullable();
            
            // For Trainees - must have experience
            $table->boolean('has_minimum_experience')->default(false); // 1+ year
            $table->boolean('has_professional_references')->default(false);
            
            // Documents
            $table->string('enrollment_certificate_path')->nullable(); // For interns
            $table->string('diploma_path')->nullable();
            $table->string('transcript_path')->nullable();
            $table->string('cv_path')->nullable();
            $table->string('training_plan_document_path')->nullable();
            $table->json('reference_letters_paths')->nullable();
            $table->string('portfolio_url')->nullable();
            
            // References
            $table->json('professional_references')->nullable(); // Minimum 2
            $table->json('academic_references')->nullable();
            
            // Additional Info
            $table->text('career_goals')->nullable();
            $table->text('why_this_field')->nullable();
            $table->json('technical_skills')->nullable();
            $table->json('software_proficiency')->nullable();
            $table->json('languages_spoken')->nullable();
            
            // Preferences
            $table->json('preferred_states')->nullable();
            $table->json('preferred_cities')->nullable();
            $table->string('company_size_preference')->nullable(); // startup, SME, corporate
            $table->boolean('willing_to_relocate')->default(true);
            
            // Validation Status
            $table->enum('validation_status', ['incomplete', 'pending_review', 'approved', 'rejected'])->default('incomplete');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('validation_completed_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes (program_type ya tiene index en línea 19)
            $table->index('validation_status');
            $table->index('industry_sector');
            $table->index('program_start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intern_trainee_validations');
    }
};
