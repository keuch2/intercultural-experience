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
        Schema::create('higher_education_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number')->unique();
            
            // Relationships
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('university_id')->constrained()->onDelete('cascade');
            
            // Program Details
            $table->enum('degree_level', ['associate', 'bachelor', 'master', 'phd'])->index();
            $table->string('major_field');
            $table->string('specific_program')->nullable();
            $table->enum('admission_term', ['fall', 'spring', 'summer'])->index();
            $table->integer('admission_year');
            $table->date('desired_start_date');
            
            // Academic Background
            $table->string('highest_degree_completed');
            $table->string('institution_name');
            $table->string('country_of_study');
            $table->integer('graduation_year');
            $table->decimal('gpa', 3, 2);
            $table->string('gpa_scale')->default('4.0');
            
            // Previous Education (JSON)
            $table->json('education_history')->nullable(); // [{degree, institution, year, gpa}]
            
            // Test Scores
            $table->integer('toefl_score')->nullable();
            $table->date('toefl_test_date')->nullable();
            $table->decimal('ielts_score', 3, 1)->nullable();
            $table->date('ielts_test_date')->nullable();
            $table->integer('sat_score')->nullable();
            $table->integer('sat_math')->nullable();
            $table->integer('sat_verbal')->nullable();
            $table->integer('gre_score')->nullable();
            $table->integer('gre_verbal')->nullable();
            $table->integer('gre_quantitative')->nullable();
            $table->integer('gmat_score')->nullable();
            
            // English Proficiency
            $table->enum('english_level', ['basic', 'intermediate', 'advanced', 'native'])->default('intermediate');
            $table->boolean('needs_esl')->default(false);
            $table->boolean('interested_pathway')->default(false);
            
            // Financial Info
            $table->enum('funding_source', ['self', 'family', 'scholarship', 'loan', 'sponsor', 'mixed'])->nullable();
            $table->decimal('available_funds_annual', 10, 2)->nullable();
            $table->boolean('needs_financial_aid')->default(false);
            $table->boolean('applying_for_scholarship')->default(false);
            $table->json('scholarship_interests')->nullable();
            
            // Work Experience (especially for graduate programs)
            $table->integer('years_work_experience')->nullable();
            $table->json('work_history')->nullable(); // [{company, position, years, description}]
            
            // Documents
            $table->string('transcript_path')->nullable();
            $table->string('diploma_path')->nullable();
            $table->string('cv_resume_path')->nullable();
            $table->string('personal_statement_path')->nullable();
            $table->string('recommendation_letter_1_path')->nullable();
            $table->string('recommendation_letter_2_path')->nullable();
            $table->string('recommendation_letter_3_path')->nullable();
            $table->string('financial_statement_path')->nullable();
            $table->string('passport_copy_path')->nullable();
            $table->json('additional_documents_paths')->nullable();
            
            // References
            $table->json('references')->nullable(); // Minimum 2-3 for graduate programs
            
            // Personal Info
            $table->text('personal_statement')->nullable();
            $table->text('why_this_program')->nullable();
            $table->text('career_goals')->nullable();
            $table->text('research_interests')->nullable(); // For graduate programs
            $table->json('extracurricular_activities')->nullable();
            $table->json('awards_honors')->nullable();
            
            // Preferences
            $table->boolean('needs_housing')->default(true);
            $table->string('housing_preference')->nullable(); // on-campus, off-campus, homestay
            $table->boolean('needs_airport_pickup')->default(false);
            $table->boolean('needs_health_insurance')->default(true);
            
            // Application Status
            $table->enum('application_status', [
                'draft',
                'submitted',
                'under_review',
                'additional_docs_required',
                'accepted',
                'conditionally_accepted',
                'waitlisted',
                'rejected',
                'deferred'
            ])->default('draft')->index();
            
            $table->date('submission_date')->nullable();
            $table->date('decision_date')->nullable();
            $table->text('decision_notes')->nullable();
            
            // Conditional Acceptance
            $table->boolean('is_conditional')->default(false);
            $table->json('conditions')->nullable(); // What needs to be met
            $table->date('conditions_deadline')->nullable();
            
            // I-20 & Visa
            $table->boolean('i20_requested')->default(false);
            $table->date('i20_issued_date')->nullable();
            $table->string('i20_document_path')->nullable();
            $table->string('sevis_id')->nullable();
            
            // Enrollment
            $table->boolean('enrollment_confirmed')->default(false);
            $table->date('enrollment_date')->nullable();
            $table->decimal('deposit_paid', 10, 2)->nullable();
            $table->date('deposit_paid_date')->nullable();
            
            // Fees
            $table->decimal('application_fee_paid', 8, 2)->nullable();
            $table->date('application_fee_paid_date')->nullable();
            
            // Tracking
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('last_status_update')->nullable();
            $table->text('internal_notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('application_status');
            $table->index('degree_level');
            $table->index('admission_term');
            $table->index(['admission_year', 'admission_term']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('higher_education_applications');
    }
};
