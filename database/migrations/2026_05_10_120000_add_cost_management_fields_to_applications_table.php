<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            // Manual override for historical loads (e.g. participants from prior years).
            // Falls back to created_at if null.
            $table->date('cost_manual_date')->nullable()->after('payment_deadline');

            // Timestamp when total_cost was first set / locked in. Used by Gestión de Pagos
            // to surface only applications that have a cost defined.
            $table->timestamp('cost_locked_at')->nullable()->after('cost_manual_date');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['cost_manual_date', 'cost_locked_at']);
        });
    }
};
