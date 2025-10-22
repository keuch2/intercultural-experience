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
        Schema::create('work_study_employers', function (Blueprint $table) {
            $table->id();
            $table->string('employer_name');
            $table->string('employer_code')->unique()->nullable();
            
            // Business Information
            $table->enum('business_type', ['restaurant', 'hotel', 'retail_store', 'cafe', 'supermarket', 'theme_park', 'resort', 'other'])->default('restaurant');
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            
            // Location
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('zip_code');
            $table->string('country')->default('USA');
            
            // Contact Information
            $table->string('contact_person_name');
            $table->string('contact_person_title')->nullable();
            $table->string('contact_email');
            $table->string('contact_phone');
            $table->string('hr_email')->nullable();
            $table->string('hr_phone')->nullable();
            
            // Work Positions Available
            $table->json('available_positions')->nullable(); // ['Server', 'Cook', 'Cashier', etc]
            $table->integer('total_positions_available')->default(0);
            $table->integer('positions_currently_filled')->default(0);
            
            // Work Details
            $table->decimal('hourly_wage_min', 8, 2)->nullable();
            $table->decimal('hourly_wage_max', 8, 2)->nullable();
            $table->integer('hours_per_week_min')->nullable();
            $table->integer('hours_per_week_max')->nullable();
            $table->json('work_schedule')->nullable(); // Flexible, Fixed, Shifts
            $table->boolean('provides_tips')->default(false);
            $table->decimal('avg_tips_per_week', 8, 2)->nullable();
            
            // Benefits & Conditions
            $table->boolean('provides_meals')->default(false);
            $table->boolean('provides_uniform')->default(false);
            $table->boolean('provides_transportation')->default(false);
            $table->boolean('provides_housing')->default(false);
            $table->decimal('housing_cost_weekly', 8, 2)->nullable();
            $table->text('other_benefits')->nullable();
            
            // Requirements
            $table->enum('min_english_level', ['beginner', 'elementary', 'intermediate', 'advanced'])->default('intermediate');
            $table->integer('min_age')->default(18);
            $table->boolean('requires_experience')->default(false);
            $table->text('special_requirements')->nullable();
            
            // Performance & Statistics
            $table->integer('students_hosted_total')->default(0);
            $table->integer('students_current')->default(0);
            $table->decimal('rating', 2, 1)->default(0.0);
            $table->integer('total_reviews')->default(0);
            $table->integer('positive_reviews')->default(0);
            $table->integer('negative_reviews')->default(0);
            
            // Partnership
            $table->boolean('is_verified')->default(false);
            $table->date('verification_date')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->integer('years_partnership')->default(0);
            
            // Legal & Compliance
            $table->string('business_license_number')->nullable();
            $table->string('tax_id')->nullable();
            $table->boolean('complies_labor_laws')->default(true);
            $table->date('last_inspection_date')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('business_type');
            $table->index(['city', 'state']);
            $table->index('is_active');
            $table->index('is_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_study_employers');
    }
};
