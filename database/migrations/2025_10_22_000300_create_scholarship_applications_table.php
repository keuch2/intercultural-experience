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
        Schema::create('scholarship_applications', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('scholarship_id')->constrained()->onDelete('cascade');
            $table->foreignId('university_application_id')->nullable()->constrained('higher_education_applications')->onDelete('set null');
            
            // Application Info
            $table->string('application_number')->unique();
            $table->date('application_date');
            
            // Documents
            $table->string('essay_path')->nullable();
            $table->string('motivation_letter_path')->nullable();
            $table->string('financial_need_statement_path')->nullable();
            $table->string('portfolio_path')->nullable();
            $table->json('additional_documents_paths')->nullable();
            
            // Essay/Statement
            $table->text('essay_text')->nullable();
            $table->text('why_deserve_scholarship')->nullable();
            $table->text('financial_need_explanation')->nullable();
            
            // Status
            $table->enum('status', [
                'draft',
                'submitted',
                'under_review',
                'shortlisted',
                'interview_scheduled',
                'awarded',
                'rejected',
                'withdrawn'
            ])->default('draft')->index();
            
            $table->date('submission_date')->nullable();
            $table->date('decision_date')->nullable();
            $table->text('decision_notes')->nullable();
            
            // Award Details (if awarded)
            $table->boolean('is_awarded')->default(false);
            $table->decimal('awarded_amount', 10, 2)->nullable();
            $table->date('award_start_date')->nullable();
            $table->date('award_end_date')->nullable();
            $table->boolean('award_accepted')->default(false);
            $table->date('award_acceptance_date')->nullable();
            
            // Interview
            $table->date('interview_date')->nullable();
            $table->time('interview_time')->nullable();
            $table->string('interview_location')->nullable();
            $table->text('interview_notes')->nullable();
            
            // Tracking
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('last_status_update')->nullable();
            $table->text('internal_notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('is_awarded');
            $table->index(['user_id', 'scholarship_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scholarship_applications');
    }
};
