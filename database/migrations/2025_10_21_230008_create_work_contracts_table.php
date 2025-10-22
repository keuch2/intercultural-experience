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
        Schema::create('work_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('job_offer_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('employer_id')->nullable()->constrained('employers')->onDelete('set null');
            
            // Información del contrato
            $table->string('contract_number')->unique();
            $table->enum('contract_type', ['seasonal', 'temporary', 'internship']);
            $table->string('position_title');
            $table->text('job_description');
            
            // Ubicación del trabajo
            $table->string('work_location_city');
            $table->string('work_location_state', 2);
            $table->string('work_location_zip', 10);
            $table->string('work_location_address');
            
            // Fechas del contrato
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration_weeks');
            $table->boolean('is_flexible_dates')->default(false);
            
            // Compensación
            $table->decimal('hourly_rate', 8, 2);
            $table->integer('hours_per_week');
            $table->decimal('overtime_rate', 8, 2)->nullable();
            $table->decimal('estimated_total_earnings', 10, 2)->nullable();
            $table->enum('payment_frequency', ['weekly', 'biweekly', 'monthly']);
            
            // Beneficios
            $table->boolean('provides_housing')->default(false);
            $table->decimal('housing_cost_per_week', 8, 2)->nullable();
            $table->text('housing_details')->nullable();
            $table->boolean('provides_meals')->default(false);
            $table->text('meals_details')->nullable();
            $table->boolean('provides_transportation')->default(false);
            $table->text('transportation_details')->nullable();
            
            // Deducciones
            $table->json('deductions')->nullable(); // Array de deducciones
            $table->decimal('total_deductions_per_week', 8, 2)->nullable();
            
            // Documentos
            $table->string('contract_pdf_path')->nullable();
            $table->string('job_offer_letter_path')->nullable();
            $table->boolean('contract_signed')->default(false);
            $table->datetime('signed_at')->nullable();
            $table->string('signature_path')->nullable();
            
            // Estado del contrato
            $table->enum('status', ['draft', 'pending_signature', 'active', 'completed', 'cancelled'])->default('draft');
            $table->text('cancellation_reason')->nullable();
            $table->date('cancellation_date')->nullable();
            
            // Validación
            $table->boolean('employer_verified')->default(false);
            $table->boolean('position_verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->datetime('verification_date')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['user_id', 'status']);
            $table->index(['employer_id', 'status']);
            $table->index('contract_number');
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_contracts');
    }
};
