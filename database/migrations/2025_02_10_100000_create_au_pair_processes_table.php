<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('au_pair_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Inscripción
            $table->date('enrollment_date')->nullable();
            $table->string('enrollment_city')->nullable();
            $table->string('enrollment_country')->nullable();

            // Etapa actual del proceso
            $table->enum('current_stage', [
                'admission', 'application', 'match_visa', 'support', 'completed', 'cancelled'
            ])->default('admission');

            // Estado por etapa
            $table->enum('admission_status', ['pending', 'in_progress', 'docs_review', 'approved'])->default('pending');
            $table->enum('application_status', ['locked', 'pending', 'in_progress', 'docs_review', 'approved'])->default('locked');
            $table->enum('match_visa_status', ['locked', 'pending', 'in_progress', 'approved'])->default('locked');
            $table->enum('support_status', ['locked', 'active', 'completed'])->default('locked');

            // Checklist de proceso (B4)
            $table->boolean('welcome_email_sent')->default(false);
            $table->boolean('interview_process_email_sent')->default(false);
            $table->boolean('all_docs_and_payments_complete')->default(false);
            $table->boolean('itep_completed')->default(false);

            // Contrato
            $table->boolean('contract_signed')->default(false);
            $table->dateTime('contract_signed_at')->nullable();
            $table->unsignedBigInteger('contract_confirmed_by')->nullable();

            // Payment gates
            $table->boolean('payment_1_verified')->default(false);
            $table->boolean('payment_2_verified')->default(false);

            // Notas generales
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('current_stage');
            $table->index('user_id');
            $table->foreign('contract_confirmed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('au_pair_processes');
    }
};
