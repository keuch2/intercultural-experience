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
        Schema::create('universities', function (Blueprint $table) {
            $table->id();
            
            // Basic Info
            $table->string('university_name');
            $table->string('code')->unique();
            $table->enum('type', ['public', 'private', 'community_college', 'technical'])->default('public');
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            
            // Location
            $table->string('address');
            $table->string('city');
            $table->string('state', 2);
            $table->string('zip_code', 10);
            $table->string('country')->default('USA');
            
            // Contact
            $table->string('main_phone');
            $table->string('admissions_email');
            $table->string('admissions_phone')->nullable();
            $table->string('international_office_email')->nullable();
            $table->string('international_office_phone')->nullable();
            
            // Academic Info
            $table->integer('founded_year')->nullable();
            $table->integer('total_students')->nullable();
            $table->integer('international_students')->nullable();
            $table->integer('undergraduate_programs')->nullable();
            $table->integer('graduate_programs')->nullable();
            $table->json('degree_types_offered')->nullable(); // Associate, Bachelor, Master, PhD
            
            // Rankings & Accreditation
            $table->string('accreditation')->nullable();
            $table->integer('us_news_ranking')->nullable();
            $table->integer('qs_world_ranking')->nullable();
            $table->decimal('acceptance_rate', 5, 2)->nullable();
            $table->decimal('graduation_rate', 5, 2)->nullable();
            
            // Academic Requirements
            $table->decimal('min_gpa_undergraduate', 3, 2)->nullable();
            $table->decimal('min_gpa_graduate', 3, 2)->nullable();
            $table->integer('min_toefl_score')->nullable();
            $table->decimal('min_ielts_score', 3, 1)->nullable();
            $table->integer('min_sat_score')->nullable();
            $table->integer('min_gre_score')->nullable();
            
            // Costs (Annual)
            $table->decimal('tuition_undergraduate_annual', 10, 2)->nullable();
            $table->decimal('tuition_graduate_annual', 10, 2)->nullable();
            $table->decimal('room_board_annual', 10, 2)->nullable();
            $table->decimal('books_supplies_annual', 10, 2)->nullable();
            $table->decimal('estimated_total_annual', 10, 2)->nullable();
            
            // Financial Aid
            $table->boolean('offers_scholarships')->default(false);
            $table->boolean('offers_financial_aid')->default(false);
            $table->boolean('offers_work_study')->default(false);
            $table->decimal('avg_scholarship_amount', 10, 2)->nullable();
            $table->integer('scholarships_available')->nullable();
            
            // Campus & Facilities
            $table->integer('campus_size_acres')->nullable();
            $table->boolean('has_on_campus_housing')->default(true);
            $table->boolean('has_library')->default(true);
            $table->boolean('has_sports_facilities')->default(true);
            $table->boolean('has_health_center')->default(true);
            $table->boolean('has_career_services')->default(true);
            
            // Program Features
            $table->json('popular_majors')->nullable();
            $table->json('language_programs')->nullable();
            $table->boolean('offers_esl')->default(false);
            $table->boolean('offers_pathway_programs')->default(false);
            $table->boolean('offers_online_programs')->default(false);
            
            // Application Details
            $table->json('application_deadlines')->nullable(); // Fall, Spring, Summer
            $table->decimal('application_fee', 8, 2)->nullable();
            $table->boolean('accepts_common_app')->default(false);
            $table->string('application_portal_url')->nullable();
            
            // International Student Support
            $table->boolean('has_international_office')->default(true);
            $table->boolean('offers_orientation')->default(true);
            $table->boolean('offers_pickup_service')->default(false);
            $table->boolean('provides_visa_support')->default(true);
            
            // Partnership & Status
            $table->boolean('is_partner_university')->default(false);
            $table->integer('years_partnership')->nullable();
            $table->integer('students_placed_total')->default(0);
            $table->integer('students_current')->default(0);
            
            // Verification
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verification_date')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Status & Rating
            $table->boolean('is_active')->default(true);
            $table->decimal('rating', 2, 1)->nullable();
            $table->integer('total_reviews')->default(0);
            
            // Documents
            $table->string('brochure_path')->nullable();
            $table->string('catalog_path')->nullable();
            $table->json('campus_photos')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('type');
            $table->index('is_active');
            $table->index('is_partner_university');
            $table->index(['city', 'state']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('universities');
    }
};
