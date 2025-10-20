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
        Schema::table('program_requisites', function (Blueprint $table) {
            $table->decimal('payment_amount', 15, 2)->nullable()->after('type');
            $table->foreignId('currency_id')->nullable()->after('payment_amount')->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_requisites', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);
            $table->dropColumn(['payment_amount', 'currency_id']);
        });
    }
};
