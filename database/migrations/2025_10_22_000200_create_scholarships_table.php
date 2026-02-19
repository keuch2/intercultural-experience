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
        Schema::create('scholarships', function (Blueprint $table) {
            $table->id();
            
            // Basic Info
            $table->string('scholarship_name');
            $table->string('code')->unique();
            $table->foreignId('university_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('scholarship_type', ['merit', 'need_based', 'athletic', 'academic', 'talent', 'diversity', 'country_specific'])->index();
            $table->text('description');
            
            // Eligibility
            $table->json('eligible_degree_levels')->nullable(); // associate, bachelor, master, phd
            $table->json('eligible_majors')->nullable();
            $table->json('eligible_countries')->nullable();
            $table->decimal('min_gpa_required', 3, 2)->nullable();
            $table->integer('min_test_score')->nullable(); // TOEFL, SAT, etc
            
            // Award Details
            $table->enum('award_type', ['full_tuition', 'partial_tuition', 'fixed_amount', 'percentage'])->default('fixed_amount');
            $table->decimal('award_amount', 10, 2)->nullable();
            $table->integer('award_percentage')->nullable();
            $table->enum('award_frequency', ['one_time', 'annual', 'semester', 'per_credit'])->default('annual');
            $table->integer('renewable_years')->nullable();
            $table->boolean('is_renewable')->default(false);
            
            // Coverage
            $table->boolean('covers_tuition')->default(true);
            $table->boolean('covers_housing')->default(false);
            $table->boolean('covers_books')->default(false);
            $table->boolean('covers_meals')->default(false);
            $table->boolean('covers_travel')->default(false);
            
            // Application
            $table->date('application_deadline')->nullable();
            $table->date('award_notification_date')->nullable();
            $table->boolean('requires_separate_application')->default(true);
            $table->string('application_url')->nullable();
            $table->json('required_documents')->nullable();
            $table->text('application_instructions')->nullable();
            
            // Availability
            $table->integer('total_awards_available')->nullable();
            $table->integer('awards_remaining')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('application_year')->nullable();
            
            // Requirements
            $table->text('special_requirements')->nullable();
            $table->boolean('requires_essay')->default(false);
            $table->boolean('requires_interview')->default(false);
            $table->boolean('requires_portfolio')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('is_active');
            $table->index('application_deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scholarships');
    }
};
