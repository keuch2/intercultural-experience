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
        Schema::create('training_plans', function (Blueprint $table) {
            $table->id();
            $table->string('plan_number')->unique();
            
            // Relationships
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('host_company_id')->constrained()->onDelete('cascade');
            $table->foreignId('validation_id')->nullable()->constrained('intern_trainee_validations')->onDelete('set null');
            
            // Plan Details
            $table->string('plan_title');
            $table->text('plan_description');
            $table->enum('program_type', ['intern', 'trainee']);
            $table->string('position_title');
            $table->string('department')->nullable();
            
            // Training Objectives
            $table->json('primary_objectives'); // Main goals
            $table->json('learning_outcomes'); // Expected results
            $table->json('skills_to_acquire'); // Technical & soft skills
            $table->json('competencies_to_develop')->nullable();
            
            // Phases/Milestones
            $table->json('training_phases')->nullable(); // [{phase, duration, activities, goals}]
            $table->json('milestones')->nullable(); // [{milestone, deadline, criteria}]
            $table->json('evaluation_criteria')->nullable();
            
            // Responsibilities
            $table->text('participant_responsibilities');
            $table->text('company_responsibilities');
            $table->text('supervisor_responsibilities')->nullable();
            
            // Supervision
            $table->string('supervisor_name');
            $table->string('supervisor_title');
            $table->string('supervisor_email');
            $table->string('supervisor_phone')->nullable();
            $table->integer('supervision_hours_per_week')->nullable();
            
            // Schedule
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_duration_months');
            $table->integer('hours_per_week');
            $table->json('work_schedule')->nullable(); // Days and hours
            
            // Location
            $table->string('training_location_address');
            $table->string('training_location_city');
            $table->string('training_location_state');
            $table->string('training_location_zip');
            $table->boolean('allows_remote_work')->default(false);
            $table->integer('remote_days_per_week')->nullable();
            
            // Compensation
            $table->boolean('is_paid')->default(false);
            $table->decimal('stipend_amount', 10, 2)->nullable();
            $table->string('stipend_frequency')->nullable();
            $table->json('benefits_included')->nullable();
            
            // Housing
            $table->boolean('provides_housing')->default(false);
            $table->text('housing_details')->nullable();
            $table->decimal('housing_cost', 10, 2)->nullable();
            
            // Evaluation & Reporting
            $table->boolean('requires_progress_reports')->default(true);
            $table->string('report_frequency')->default('monthly'); // weekly, bi-weekly, monthly
            $table->boolean('requires_final_presentation')->default(false);
            $table->boolean('offers_certificate')->default(false);
            $table->string('certificate_type')->nullable();
            
            // Approvals
            $table->boolean('company_approved')->default(false);
            $table->timestamp('company_approved_at')->nullable();
            $table->foreignId('company_approved_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->boolean('participant_approved')->default(false);
            $table->timestamp('participant_approved_at')->nullable();
            
            $table->boolean('sponsor_approved')->default(false);
            $table->timestamp('sponsor_approved_at')->nullable();
            $table->foreignId('sponsor_approved_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Status
            $table->enum('status', [
                'draft',
                'pending_company_approval',
                'pending_participant_approval',
                'pending_sponsor_approval',
                'approved',
                'active',
                'completed',
                'terminated',
                'cancelled'
            ])->default('draft');
            
            // Tracking
            $table->integer('completion_percentage')->default(0);
            $table->json('completed_milestones')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->text('completion_notes')->nullable();
            
            // Documents
            $table->string('plan_document_path')->nullable();
            $table->string('signed_plan_path')->nullable();
            $table->json('progress_reports_paths')->nullable();
            $table->string('final_report_path')->nullable();
            $table->string('certificate_path')->nullable();
            
            // Cancellation
            $table->text('termination_reason')->nullable();
            $table->date('termination_date')->nullable();
            $table->foreignId('terminated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('status');
            $table->index('program_type');
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_plans');
    }
};
