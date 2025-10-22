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
        Schema::create('language_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('program_number')->unique();
            
            // Language School Information
            $table->string('school_name');
            $table->string('school_city');
            $table->string('school_state');
            $table->string('school_country')->default('USA');
            $table->string('school_address')->nullable();
            $table->string('school_website')->nullable();
            $table->string('school_phone')->nullable();
            $table->enum('school_accreditation', ['CEA', 'ACCET', 'IALC', 'Quality English', 'other', 'none'])->default('none');
            
            // Language & Level
            $table->enum('language', ['english', 'spanish', 'french', 'german', 'italian', 'portuguese', 'mandarin', 'other'])->default('english');
            $table->enum('current_level', ['beginner', 'elementary', 'pre_intermediate', 'intermediate', 'upper_intermediate', 'advanced', 'proficiency'])->default('intermediate');
            $table->enum('target_level', ['elementary', 'pre_intermediate', 'intermediate', 'upper_intermediate', 'advanced', 'proficiency'])->default('advanced');
            
            // Program Details
            $table->enum('program_type', [
                'general_language',
                'intensive',
                'super_intensive',
                'business_language',
                'exam_preparation',
                'academic_language',
                'conversation_only',
                'private_lessons',
                'language_plus_activity'
            ])->default('general_language');
            
            $table->string('exam_type')->nullable(); // TOEFL, IELTS, TOEIC, Cambridge, DELE, etc
            $table->string('activity_type')->nullable(); // For language+activity programs
            
            $table->integer('weeks_duration');
            $table->integer('hours_per_week');
            $table->integer('class_size_max')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            
            // Costs
            $table->decimal('tuition_fee', 10, 2);
            $table->decimal('registration_fee', 8, 2)->default(0);
            $table->decimal('materials_fee', 8, 2)->default(0);
            $table->decimal('exam_fee', 8, 2)->nullable();
            $table->decimal('activity_fee', 8, 2)->nullable();
            
            // Accommodation
            $table->enum('accommodation_type', ['homestay', 'student_residence', 'hotel', 'apartment', 'none', 'self_arranged'])->default('homestay');
            $table->boolean('accommodation_included')->default(true);
            $table->decimal('accommodation_cost_weekly', 8, 2)->nullable();
            $table->boolean('meals_included')->default(false);
            $table->enum('meal_plan', ['none', 'breakfast', 'half_board', 'full_board'])->default('none');
            
            // Additional Services
            $table->boolean('airport_transfer')->default(false);
            $table->decimal('airport_transfer_cost', 8, 2)->nullable();
            $table->boolean('insurance_included')->default(true);
            $table->decimal('insurance_cost', 8, 2)->nullable();
            $table->boolean('study_materials_included')->default(true);
            
            // Total Cost
            $table->decimal('total_program_cost', 10, 2);
            
            // Documents
            $table->string('acceptance_letter_path')->nullable();
            $table->string('visa_support_letter_path')->nullable();
            $table->string('payment_receipt_path')->nullable();
            $table->string('certificate_path')->nullable();
            
            // Placement Test
            $table->integer('placement_test_score')->nullable();
            $table->date('placement_test_date')->nullable();
            $table->string('assigned_level')->nullable();
            
            // Progress Tracking
            $table->integer('attendance_percentage')->default(0);
            $table->integer('completed_weeks')->default(0);
            $table->text('teacher_feedback')->nullable();
            $table->enum('progress_rating', ['excellent', 'good', 'satisfactory', 'needs_improvement', 'not_rated'])->default('not_rated');
            
            // Status & Workflow
            $table->enum('status', [
                'draft',
                'submitted',
                'under_review',
                'accepted',
                'enrolled',
                'active',
                'completed',
                'cancelled',
                'rejected'
            ])->default('draft');
            
            $table->date('submission_date')->nullable();
            $table->date('acceptance_date')->nullable();
            $table->date('enrollment_date')->nullable();
            $table->date('completion_date')->nullable();
            
            // Additional Info
            $table->text('learning_goals')->nullable();
            $table->text('special_requirements')->nullable();
            $table->text('dietary_restrictions')->nullable();
            $table->text('internal_notes')->nullable();
            
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('last_status_update')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('status');
            $table->index('language');
            $table->index('program_type');
            $table->index(['school_city', 'school_state']);
            $table->index('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('language_programs');
    }
};
