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
            $table->foreignId('application_id')->unique()->constrained()->onDelete('cascade');
            $table->enum('current_status', [
                'documentation_pending',
                'sponsor_interview_pending',
                'sponsor_interview_approved',
                'job_interview_pending',
                'job_interview_approved',
                'ds160_pending',
                'ds160_completed',
                'ds2019_pending',
                'ds2019_received',
                'sevis_paid',
                'consular_fee_paid',
                'appointment_scheduled',
                'in_correspondence',
                'visa_approved',
                'visa_rejected'
            ])->default('documentation_pending');
            $table->string('ds160_number', 50)->nullable();
            $table->string('ds2019_number', 50)->nullable();
            $table->string('sevis_id', 50)->nullable();
            $table->decimal('sevis_amount', 10, 2)->nullable();
            $table->timestamp('sevis_paid_at')->nullable();
            $table->decimal('consular_fee_amount', 10, 2)->nullable();
            $table->timestamp('consular_fee_paid_at')->nullable();
            $table->timestamp('appointment_date')->nullable();
            $table->string('appointment_location')->nullable();
            $table->enum('interview_result', ['approved', 'rejected', 'pending', 'administrative_processing'])->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index('application_id');
            $table->index('current_status');
            $table->index('appointment_date');
            $table->index('interview_result');
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
