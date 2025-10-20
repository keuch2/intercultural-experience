<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabla para sistema de cuotas de pago
     */
    public function up(): void
    {
        Schema::create('payment_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade'); // Aplicación
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Participante
            $table->foreignId('program_id')->constrained()->onDelete('cascade'); // Programa
            
            // Configuración del plan de cuotas
            $table->string('plan_name'); // Nombre del plan (ej: "3 cuotas sin interés")
            $table->integer('total_installments'); // Número total de cuotas
            $table->decimal('total_amount', 15, 2); // Monto total
            $table->decimal('interest_rate', 5, 2)->default(0); // Tasa de interés %
            $table->foreignId('currency_id')->nullable()->constrained(); // Moneda
            
            // Estado general del plan
            $table->enum('status', ['active', 'completed', 'defaulted', 'cancelled'])->default('active');
            
            // Auditoría
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Índices
            $table->index('application_id');
            $table->index('user_id');
            $table->index('status');
        });
        
        // Tabla detalle de cada cuota
        Schema::create('installment_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_installment_id')->constrained()->onDelete('cascade');
            
            // Datos de la cuota
            $table->integer('installment_number'); // Número de cuota (1, 2, 3...)
            $table->decimal('amount', 15, 2); // Monto de esta cuota
            $table->date('due_date'); // Fecha de vencimiento
            $table->date('paid_date')->nullable(); // Fecha de pago
            
            // Estado
            $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])->default('pending');
            
            // Pago asociado
            $table->foreignId('user_program_requisite_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null');
            
            // Recargos por mora
            $table->decimal('late_fee', 15, 2)->default(0);
            
            $table->timestamps();
            
            // Índices
            $table->index('payment_installment_id');
            $table->index(['status', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installment_details');
        Schema::dropIfExists('payment_installments');
    }
};
