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
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            
            // Información básica
            $table->string('school_name');
            $table->enum('school_type', ['public', 'private', 'charter', 'religious', 'international']);
            $table->string('district_name')->nullable();
            $table->string('school_code')->nullable();
            
            // Ubicación
            $table->string('address');
            $table->string('city');
            $table->string('state', 2);
            $table->string('zip_code', 10);
            $table->string('country')->default('USA');
            $table->string('phone');
            $table->string('email');
            $table->string('website')->nullable();
            
            // Contacto principal
            $table->string('principal_name');
            $table->string('principal_email');
            $table->string('hr_contact_name')->nullable();
            $table->string('hr_contact_email')->nullable();
            $table->string('hr_contact_phone')->nullable();
            
            // Información académica
            $table->json('grade_levels')->nullable(); // ['K-5', '6-8', '9-12']
            $table->integer('total_students')->nullable();
            $table->integer('total_teachers')->nullable();
            $table->decimal('student_teacher_ratio', 5, 2)->nullable();
            $table->json('subjects_needed')->nullable(); // Array de materias que necesitan
            
            // Programa de intercambio
            $table->integer('years_in_program')->default(0);
            $table->integer('teachers_hired_total')->default(0);
            $table->integer('teachers_hired_current_year')->default(0);
            $table->integer('positions_available')->default(0);
            
            // Requisitos y preferencias
            $table->json('required_certifications')->nullable();
            $table->integer('minimum_experience_years')->default(0);
            $table->json('preferred_nationalities')->nullable();
            $table->boolean('sponsors_visa')->default(true);
            $table->boolean('provides_housing_assistance')->default(false);
            $table->text('housing_details')->nullable();
            
            // Compensación
            $table->decimal('salary_range_min', 10, 2)->nullable();
            $table->decimal('salary_range_max', 10, 2)->nullable();
            $table->json('benefits_offered')->nullable(); // Array de beneficios
            
            // Estado y validación
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->date('verification_date')->nullable();
            $table->decimal('rating', 3, 2)->nullable(); // 0.00 - 5.00
            $table->integer('total_reviews')->default(0);
            
            // Documentos
            $table->string('accreditation_certificate_path')->nullable();
            $table->string('agreement_path')->nullable();
            
            // Notas y observaciones
            $table->text('notes')->nullable();
            $table->text('special_requirements')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('school_name');
            $table->index(['state', 'is_active']);
            $table->index('school_type');
            $table->index('is_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
