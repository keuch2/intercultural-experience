<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabla para almacenar recibos/facturas de pagos
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // Número de factura único
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Participante
            $table->foreignId('application_id')->nullable()->constrained()->onDelete('set null'); // Aplicación relacionada
            $table->foreignId('program_id')->nullable()->constrained()->onDelete('set null'); // Programa
            $table->foreignId('user_program_requisite_id')->nullable()->constrained()->onDelete('set null'); // Pago requisito
            
            // Datos de facturación
            $table->string('billing_name'); // Nombre para factura
            $table->string('billing_email'); // Email para factura
            $table->string('billing_address')->nullable(); // Dirección
            $table->string('billing_city')->nullable(); // Ciudad
            $table->string('billing_country')->nullable(); // País
            $table->string('billing_tax_id')->nullable(); // RUC/Tax ID
            
            // Montos
            $table->decimal('subtotal', 15, 2); // Subtotal
            $table->decimal('tax_amount', 15, 2)->default(0); // Impuestos
            $table->decimal('discount_amount', 15, 2)->default(0); // Descuentos
            $table->decimal('total', 15, 2); // Total final
            $table->foreignId('currency_id')->nullable()->constrained(); // Moneda
            
            // Concepto
            $table->string('concept'); // Concepto de pago
            $table->text('notes')->nullable(); // Notas adicionales
            
            // Estado
            $table->enum('status', ['draft', 'issued', 'paid', 'cancelled', 'refunded'])->default('draft');
            $table->date('issue_date'); // Fecha de emisión
            $table->date('due_date')->nullable(); // Fecha de vencimiento
            $table->date('paid_date')->nullable(); // Fecha de pago
            
            // Archivos
            $table->string('pdf_path')->nullable(); // Ruta del PDF generado
            
            // Auditoría
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // Admin que creó
            $table->timestamps();
            
            // Índices
            $table->index('invoice_number');
            $table->index('user_id');
            $table->index(['status', 'issue_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
