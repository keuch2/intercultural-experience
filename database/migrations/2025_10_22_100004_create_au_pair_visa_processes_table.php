<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('au_pair_visa_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('au_pair_process_id')->unique()->constrained()->onDelete('cascade');

            // C1: Aplicación
            $table->boolean('visa_email_sent')->default(false);
            $table->string('visa_form_path')->nullable();
            $table->string('visa_photo_path')->nullable();
            $table->boolean('consular_fee_paid')->default(false);
            $table->boolean('appointment_scheduled')->default(false);
            $table->boolean('documents_sent_for_appointment')->default(false);

            // C2: Cita
            $table->date('appointment_date')->nullable();
            $table->time('appointment_time')->nullable();
            $table->string('embassy')->nullable();

            // C3: Docs IE
            $table->string('ds160_path')->nullable();
            $table->string('ds2019_path')->nullable();
            $table->string('participation_letter_path')->nullable();
            $table->string('appointment_instructions_path')->nullable();
            $table->boolean('document_check_completed')->default(false);
            $table->dateTime('document_check_completed_at')->nullable();

            // C4: Resultado
            $table->enum('interview_result', ['pending', 'approved', 'denied', 'administrative_process'])->default('pending');
            $table->text('interview_result_notes')->nullable();

            // C5: Viaje
            $table->dateTime('departure_datetime')->nullable();
            $table->dateTime('arrival_usa_datetime')->nullable();
            $table->json('flight_info')->nullable();

            // C6: Orientación
            $table->date('pre_departure_orientation_date')->nullable();
            $table->boolean('pre_departure_orientation_completed')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('au_pair_visa_processes');
    }
};
