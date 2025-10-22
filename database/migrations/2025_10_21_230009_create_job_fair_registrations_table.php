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
        Schema::create('job_fair_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_fair_event_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('cascade');
            
            // Tipo de participante
            $table->enum('participant_type', ['teacher', 'school', 'observer']);
            
            // Información de registro
            $table->string('registration_number')->unique();
            $table->datetime('registered_at');
            $table->enum('registration_status', ['pending', 'confirmed', 'cancelled', 'attended', 'no_show'])->default('pending');
            
            // Información de pago (si aplica)
            $table->boolean('payment_required')->default(false);
            $table->decimal('amount_paid', 8, 2)->nullable();
            $table->datetime('payment_date')->nullable();
            $table->string('payment_reference')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'refunded'])->nullable();
            
            // Documentos presentados
            $table->json('submitted_documents')->nullable();
            $table->boolean('documents_verified')->default(false);
            $table->datetime('documents_verified_at')->nullable();
            
            // Preferencias (para teachers)
            $table->json('preferred_schools')->nullable(); // IDs de escuelas preferidas
            $table->json('interview_slots')->nullable(); // Horarios disponibles para entrevistas
            
            // Actividad durante el evento
            $table->datetime('checked_in_at')->nullable();
            $table->integer('interviews_completed')->default(0);
            $table->json('interviewed_with')->nullable(); // Array de school_ids
            $table->integer('offers_received')->default(0);
            $table->json('offers_from')->nullable(); // Array de school_ids con ofertas
            
            // Resultado final
            $table->boolean('placement_successful')->default(false);
            $table->foreignId('placed_at_school_id')->nullable()->constrained('schools');
            $table->date('placement_date')->nullable();
            
            // Feedback
            $table->integer('satisfaction_rating')->nullable(); // 1-5
            $table->text('feedback')->nullable();
            
            $table->timestamps();
            
            $table->unique(['job_fair_event_id', 'user_id']);
            $table->unique(['job_fair_event_id', 'school_id']);
            $table->index('registration_status');
            $table->index('participant_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_fair_registrations');
    }
};
