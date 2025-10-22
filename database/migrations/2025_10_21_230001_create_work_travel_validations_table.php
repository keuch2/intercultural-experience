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
        Schema::create('work_travel_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('application_id')->nullable()->constrained()->onDelete('cascade');
            
            // Validación Universidad
            $table->string('university_name');
            $table->enum('study_type', ['presencial', 'online', 'hybrid']);
            $table->boolean('is_presencial_validated')->default(false);
            $table->date('validation_date')->nullable();
            $table->string('student_id_number')->nullable();
            $table->string('enrollment_certificate_path')->nullable();
            $table->date('expected_graduation')->nullable();
            $table->integer('current_semester');
            $table->integer('total_semesters');
            
            // Validación Académica
            $table->decimal('gpa', 3, 2)->nullable();
            $table->boolean('is_full_time_student')->default(true);
            $table->integer('weekly_class_hours')->nullable();
            $table->json('current_courses')->nullable(); // Array de cursos actuales
            
            // Período del programa
            $table->date('program_start_date');
            $table->date('program_end_date');
            $table->enum('season', ['summer', 'winter']);
            
            // Validación de elegibilidad
            $table->boolean('meets_age_requirement')->default(false); // 18-28 años
            $table->boolean('meets_academic_requirement')->default(false);
            $table->boolean('meets_english_requirement')->default(false);
            $table->boolean('has_valid_passport')->default(false);
            $table->boolean('has_clean_record')->default(false);
            
            // Estado de validación
            $table->enum('validation_status', ['pending', 'approved', 'rejected', 'incomplete'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['user_id', 'validation_status']);
            $table->index('study_type');
            $table->index('season');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_travel_validations');
    }
};
