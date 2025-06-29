<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['income', 'expense']); // ingreso o egreso
            $table->string('category'); // categoría del ingreso/egreso
            $table->string('description');
            $table->decimal('amount', 15, 2); // monto en la moneda original
            $table->foreignId('currency_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('amount_pyg', 15, 2)->nullable(); // monto convertido a guaraníes
            $table->date('transaction_date'); // fecha real de la transacción
            $table->string('reference')->nullable(); // número de factura, recibo, etc.
            $table->string('payment_method')->nullable(); // efectivo, transferencia, tarjeta, etc.
            $table->foreignId('application_id')->nullable()->constrained()->onDelete('set null'); // si está relacionado a una aplicación
            $table->foreignId('program_id')->nullable()->constrained()->onDelete('set null'); // si está relacionado a un programa
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // quien hizo el pago (para ingresos)
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // admin que registró la transacción
            $table->text('notes')->nullable();
            $table->string('receipt_file')->nullable(); // archivo de comprobante
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('confirmed');
            $table->timestamps();
            
            // Índices
            $table->index(['type', 'transaction_date']);
            $table->index(['category', 'type']);
            $table->index('transaction_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
}; 