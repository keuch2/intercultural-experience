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
        Schema::create('work_study_placements', function (Blueprint $table) {
            $table->id();
            $table->string('placement_number')->unique();
            
            // Relationships
            $table->foreignId('program_id')->constrained('work_study_programs')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('employer_id')->constrained('work_study_employers')->onDelete('cascade');
            
            // Position Details
            $table->string('job_title');
            $table->text('job_description')->nullable();
            $table->json('job_responsibilities')->nullable();
            
            // Work Schedule
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('hours_per_week');
            $table->json('work_days')->nullable(); // Mon, Tue, Wed, etc
            $table->string('shift_type')->nullable(); // Morning, Evening, Night, Rotating
            
            // Compensation
            $table->decimal('hourly_wage', 8, 2);
            $table->boolean('receives_tips')->default(false);
            $table->decimal('avg_tips_weekly', 8, 2)->nullable();
            $table->decimal('estimated_weekly_earnings', 8, 2);
            $table->decimal('total_earnings_to_date', 10, 2)->default(0);
            
            // Benefits
            $table->boolean('meals_provided')->default(false);
            $table->boolean('uniform_provided')->default(false);
            $table->boolean('transportation_provided')->default(false);
            $table->boolean('housing_provided')->default(false);
            $table->decimal('housing_cost_deduction', 8, 2)->nullable();
            
            // Performance Tracking
            $table->integer('total_hours_worked')->default(0);
            $table->integer('weeks_completed')->default(0);
            $table->enum('attendance_rating', ['excellent', 'good', 'fair', 'poor'])->nullable();
            $table->enum('performance_rating', ['excellent', 'good', 'fair', 'poor', 'not_rated'])->default('not_rated');
            $table->text('supervisor_feedback')->nullable();
            
            // Issues & Reports
            $table->integer('incidents_reported')->default(0);
            $table->integer('warnings_issued')->default(0);
            $table->text('issues_notes')->nullable();
            
            // Status
            $table->enum('status', [
                'pending',
                'approved',
                'active',
                'on_hold',
                'completed',
                'terminated_student',
                'terminated_employer',
                'cancelled'
            ])->default('pending');
            
            $table->date('activation_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->text('termination_reason')->nullable();
            $table->foreignId('terminated_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Documents
            $table->string('job_offer_letter_path')->nullable();
            $table->string('work_permit_path')->nullable();
            $table->string('contract_signed_path')->nullable();
            $table->string('performance_review_path')->nullable();
            
            // Contact at Workplace
            $table->string('supervisor_name')->nullable();
            $table->string('supervisor_phone')->nullable();
            $table->string('supervisor_email')->nullable();
            
            // Evaluation
            $table->integer('student_rating')->nullable(); // 1-5 stars from employer
            $table->integer('employer_rating')->nullable(); // 1-5 stars from student
            $table->text('student_review')->nullable();
            $table->text('employer_review')->nullable();
            
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('internal_notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('status');
            $table->index(['start_date', 'end_date']);
            $table->index('employer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_study_placements');
    }
};
