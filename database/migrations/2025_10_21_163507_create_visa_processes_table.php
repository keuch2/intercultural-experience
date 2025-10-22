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
        Schema::create('visa_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('application_id')->nullable()->constrained()->onDelete('cascade');
            
            // Documentación completa
            $table->boolean('documentation_complete')->default(false);
            $table->date('documentation_complete_date')->nullable();
            
            // Sponsor Interview
            $table->enum('sponsor_interview_status', ['pending', 'scheduled', 'approved', 'rejected'])->default('pending');
            $table->dateTime('sponsor_interview_date')->nullable();
            $table->text('sponsor_interview_notes')->nullable();
            
            // Job Interview
            $table->enum('job_interview_status', ['pending', 'scheduled', 'approved', 'rejected'])->default('pending');
            $table->dateTime('job_interview_date')->nullable();
            $table->text('job_interview_notes')->nullable();
            
            // DS160
            $table->boolean('ds160_completed')->default(false);
            $table->date('ds160_completed_date')->nullable();
            $table->string('ds160_confirmation_number')->nullable();
            $table->string('ds160_file_path')->nullable();
            
            // DS2019
            $table->boolean('ds2019_received')->default(false);
            $table->date('ds2019_received_date')->nullable();
            $table->string('ds2019_file_path')->nullable();
            
            // SEVIS
            $table->boolean('sevis_paid')->default(false);
            $table->date('sevis_paid_date')->nullable();
            $table->decimal('sevis_amount', 10, 2)->nullable();
            $table->string('sevis_receipt_path')->nullable();
            
            // Tasa Consular
            $table->boolean('consular_fee_paid')->default(false);
            $table->date('consular_fee_paid_date')->nullable();
            $table->decimal('consular_fee_amount', 10, 2)->nullable();
            $table->string('consular_fee_receipt_path')->nullable();
            
            // Cita Consular
            $table->boolean('consular_appointment_scheduled')->default(false);
            $table->dateTime('consular_appointment_date')->nullable();
            $table->string('consular_appointment_location')->nullable();
            
            // Resultado Final
            $table->enum('visa_result', ['pending', 'approved', 'correspondence', 'rejected'])->nullable();
            $table->date('visa_result_date')->nullable();
            $table->text('visa_result_notes')->nullable();
            
            // Documentos adicionales
            $table->string('passport_file_path')->nullable();
            $table->string('visa_photo_path')->nullable();
            
            // Notas generales del proceso
            $table->text('process_notes')->nullable();
            
            // Estado general calculado
            $table->string('current_step')->default('documentation');
            $table->integer('progress_percentage')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices para búsquedas rápidas
            $table->index('user_id');
            $table->index('current_step');
            $table->index('visa_result');
            $table->index('consular_appointment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visa_processes');
    }
};
