<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Módulo 18: Add exchange rate and converted amount fields for multi-currency payments.
 * When a payment is made in a different currency than the program cost,
 * exchange_rate stores the conversion rate and converted_amount stores the
 * equivalent amount in the program's currency.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('exchange_rate', 15, 4)->nullable()->after('amount')
                  ->comment('Exchange rate at time of payment (e.g., 1 USD = 7500 PYG)');
            $table->decimal('converted_amount', 10, 2)->nullable()->after('exchange_rate')
                  ->comment('Amount converted to program currency');
            $table->date('payment_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['exchange_rate', 'converted_amount']);
        });
    }
};
