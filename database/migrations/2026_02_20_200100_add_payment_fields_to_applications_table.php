<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->enum('cost_currency', ['USD', 'PYG'])->default('USD')->after('total_cost');
            $table->date('payment_deadline')->nullable()->after('amount_paid');
            $table->decimal('exchange_rate', 12, 2)->nullable()->after('payment_deadline');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['cost_currency', 'payment_deadline', 'exchange_rate']);
        });
    }
};
