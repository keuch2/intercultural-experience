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
        Schema::table('users', function (Blueprint $table) {
            // Datos personales extendidos
            $table->string('ci_number')->nullable()->after('email');
            $table->string('passport_number')->nullable();
            $table->date('passport_expiry')->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->string('skype')->nullable();
            $table->string('instagram')->nullable();
            
            // Datos académicos
            $table->string('university')->nullable();
            $table->string('career')->nullable();
            $table->string('academic_year')->nullable();
            $table->enum('study_modality', ['presencial', 'online', 'hybrid'])->nullable();
            
            // Datos laborales
            $table->string('current_job')->nullable();
            $table->string('job_position')->nullable();
            $table->string('work_address')->nullable();
            
            // Experiencia USA
            $table->boolean('has_been_to_usa')->default(false);
            $table->integer('usa_times')->default(0);
            $table->boolean('has_relatives_in_usa')->default(false);
            $table->string('relatives_in_usa_location')->nullable();
            $table->string('previous_visa_type')->nullable();
            $table->boolean('visa_denied')->default(false);
            $table->boolean('entry_denied')->default(false);
            $table->text('visa_denial_reason')->nullable();
            
            // Au Pair específico
            $table->boolean('smoker')->default(false);
            $table->boolean('has_drivers_license')->default(false);
            $table->integer('driving_years')->default(0);
            $table->boolean('can_swim')->default(false);
            $table->boolean('first_aid_certified')->default(false);
            $table->boolean('cpr_certified')->default(false);
            
            // Teachers específico
            $table->string('mec_registration')->nullable();
            $table->string('teaching_degree')->nullable();
            $table->integer('teaching_years')->default(0);
            
            // Expectativas
            $table->text('program_expectations')->nullable();
            $table->json('hobbies')->nullable();
            
            $table->index('university');
            $table->index('study_modality');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'ci_number', 'passport_number', 'passport_expiry', 'marital_status',
                'skype', 'instagram', 'university', 'career', 'academic_year',
                'study_modality', 'current_job', 'job_position', 'work_address',
                'has_been_to_usa', 'usa_times', 'has_relatives_in_usa',
                'relatives_in_usa_location', 'previous_visa_type', 'visa_denied',
                'entry_denied', 'visa_denial_reason', 'smoker', 'has_drivers_license',
                'driving_years', 'can_swim', 'first_aid_certified', 'cpr_certified',
                'mec_registration', 'teaching_degree', 'teaching_years',
                'program_expectations', 'hobbies'
            ]);
        });
    }
};
