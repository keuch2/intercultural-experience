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
        Schema::create('work_study_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('program_number')->unique();
            
            // Language School Information
            $table->string('language_school_name');
            $table->string('school_city');
            $table->string('school_state');
            $table->string('school_address')->nullable();
            $table->string('school_website')->nullable();
            $table->enum('school_accreditation', ['CEA', 'ACCET', 'other', 'none'])->default('none');
            
            // Program Details
            $table->enum('program_type', ['intensive_english', 'semi_intensive', 'academic_english', 'business_english'])->default('intensive_english');
            $table->integer('weeks_of_study');
            $table->integer('hours_per_week');
            $table->date('program_start_date');
            $table->date('program_end_date');
            $table->decimal('tuition_cost', 10, 2);
            $table->string('program_level')->nullable(); // Beginner, Intermediate, Advanced
            
            // English Proficiency
            $table->enum('current_english_level', ['beginner', 'elementary', 'pre_intermediate', 'intermediate', 'upper_intermediate', 'advanced'])->default('intermediate');
            $table->integer('english_test_score')->nullable();
            $table->string('english_test_type')->nullable(); // TOEFL, IELTS, etc
            $table->date('test_date')->nullable();
            
            // Work Component
            $table->boolean('includes_work_component')->default(true);
            $table->integer('work_hours_per_week')->nullable();
            $table->enum('work_category', ['hospitality', 'retail', 'food_service', 'customer_service', 'administrative', 'other'])->nullable();
            $table->text('work_preferences')->nullable();
            $table->decimal('expected_hourly_wage', 8, 2)->nullable();
            
            // Accommodation
            $table->enum('accommodation_type', ['homestay', 'student_residence', 'shared_apartment', 'hotel', 'self_arranged'])->default('homestay');
            $table->boolean('accommodation_included')->default(true);
            $table->decimal('accommodation_cost_weekly', 8, 2)->nullable();
            $table->text('accommodation_preferences')->nullable();
            
            // Insurance & Costs
            $table->boolean('insurance_included')->default(true);
            $table->decimal('insurance_cost', 10, 2)->nullable();
            $table->decimal('total_program_cost', 10, 2);
            $table->decimal('registration_fee', 8, 2)->default(0);
            $table->decimal('materials_fee', 8, 2)->default(0);
            
            // Documents
            $table->string('acceptance_letter_path')->nullable();
            $table->string('i20_document_path')->nullable();
            $table->string('sevis_id')->nullable();
            $table->date('sevis_fee_paid_date')->nullable();
            $table->string('payment_receipt_path')->nullable();
            
            // Status & Workflow
            $table->enum('status', [
                'draft',
                'submitted',
                'under_review',
                'accepted',
                'i20_issued',
                'visa_interview_scheduled',
                'visa_approved',
                'active',
                'completed',
                'cancelled',
                'rejected'
            ])->default('draft');
            
            $table->date('submission_date')->nullable();
            $table->date('acceptance_date')->nullable();
            $table->date('i20_issue_date')->nullable();
            $table->date('visa_interview_date')->nullable();
            $table->date('visa_approval_date')->nullable();
            
            // Placement Info (when matched with employer)
            $table->foreignId('employer_id')->nullable()->constrained('work_study_employers')->onDelete('set null');
            $table->date('work_start_date')->nullable();
            $table->date('work_end_date')->nullable();
            
            // Additional Info
            $table->text('special_requirements')->nullable();
            $table->text('dietary_restrictions')->nullable();
            $table->text('internal_notes')->nullable();
            
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('last_status_update')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('status');
            $table->index('program_start_date');
            $table->index(['school_city', 'school_state']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_study_programs');
    }
};
