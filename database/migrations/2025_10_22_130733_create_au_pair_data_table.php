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
        Schema::create('au_pair_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained()->onDelete('cascade');
            
            // Experiencia con Niños (CRÍTICO)
            $table->text('childcare_experience_detailed')->nullable(); // Experiencia detallada
            $table->text('ages_cared_for')->nullable(); // JSON de edades
            $table->text('experience_durations')->nullable(); // JSON de duraciones
            $table->text('care_types')->nullable(); // niñera, maestra, familiar
            $table->boolean('cared_for_babies')->default(false); // < 2 años
            $table->boolean('special_needs_experience')->default(false);
            $table->text('special_needs_details')->nullable();
            
            // Certificaciones
            $table->boolean('has_first_aid_cert')->default(false);
            $table->string('first_aid_cert_path')->nullable();
            $table->date('first_aid_cert_expiry')->nullable();
            
            $table->boolean('has_cpr_cert')->default(false);
            $table->string('cpr_cert_path')->nullable();
            $table->date('cpr_cert_expiry')->nullable();
            
            $table->text('other_certifications')->nullable();
            
            // Cartas de Referencia (Mínimo 3)
            $table->json('references')->nullable(); // Array de referencias
            
            // Fotos y Video
            $table->json('photos')->nullable(); // Array de paths (mínimo 6)
            $table->string('presentation_video_path')->nullable();
            $table->string('dear_host_family_letter_path')->nullable();
            
            // Licencia de Conducir
            $table->boolean('has_drivers_license')->default(false);
            $table->string('drivers_license_number')->nullable();
            $table->string('drivers_license_country')->nullable();
            $table->date('drivers_license_expiry')->nullable();
            $table->string('drivers_license_path')->nullable();
            
            // Familia Host (Post-matching)
            $table->unsignedBigInteger('host_family_id')->nullable();
            $table->string('host_family_city')->nullable();
            $table->string('host_family_state')->nullable();
            $table->string('host_family_country')->nullable();
            $table->integer('number_of_children')->nullable();
            $table->json('children_ages')->nullable();
            $table->boolean('children_special_needs')->default(false);
            $table->text('children_special_needs_details')->nullable();
            $table->boolean('has_pets')->default(false);
            $table->text('pets_details')->nullable();
            $table->string('work_schedule')->nullable();
            $table->date('start_date_with_family')->nullable();
            
            // Perfil en agencia
            $table->boolean('profile_active')->default(false);
            $table->date('profile_activated_at')->nullable();
            $table->integer('family_interviews')->default(0);
            $table->date('matched_at')->nullable();
            
            // Evaluación de inglés
            $table->enum('english_level', ['basic', 'intermediate', 'advanced'])->nullable();
            $table->enum('cefr_level', ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'])->nullable();
            
            // Etapa Actual del Proceso
            $table->enum('current_stage', [
                'registration',
                'profile_creation',
                'documentation',
                'profile_review',
                'profile_active',
                'matching',
                'family_interviews',
                'match_confirmed',
                'visa_process',
                'training',
                'travel',
                'in_program',
                'completed'
            ])->default('registration');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('application_id');
            $table->index('current_stage');
            $table->index('profile_active');
            $table->index('host_family_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('au_pair_data');
    }
};
