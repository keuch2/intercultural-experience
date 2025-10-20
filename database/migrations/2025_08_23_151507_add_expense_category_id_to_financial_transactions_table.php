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
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->foreignId('expense_category_id')->nullable()->after('category')->constrained('expense_categories')->onDelete('set null');
            $table->decimal('tax_amount', 15, 2)->nullable()->after('amount_pyg'); // Monto de impuestos
            $table->string('supplier')->nullable()->after('payment_method'); // Proveedor para egresos
            $table->date('due_date')->nullable()->after('transaction_date'); // Fecha de vencimiento
            $table->boolean('is_recurring')->default(false)->after('status'); // Si es recurrente
            $table->string('recurring_period')->nullable()->after('is_recurring'); // monthly, weekly, yearly
            
            $table->index('expense_category_id');
            $table->index(['type', 'expense_category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->dropForeign(['expense_category_id']);
            $table->dropColumn([
                'expense_category_id',
                'tax_amount', 
                'supplier',
                'due_date',
                'is_recurring',
                'recurring_period'
            ]);
        });
    }
};
