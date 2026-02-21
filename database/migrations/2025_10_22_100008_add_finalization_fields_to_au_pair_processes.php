<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('au_pair_processes', function (Blueprint $table) {
            $table->enum('finalization_result', ['success', 'not_success', 'status_change', 'other'])->nullable()->after('support_status');
            $table->text('finalization_reason')->nullable()->after('finalization_result');
            $table->date('finalization_date')->nullable()->after('finalization_reason');
            $table->foreignId('finalized_by')->nullable()->after('finalization_date')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('au_pair_processes', function (Blueprint $table) {
            $table->dropForeign(['finalized_by']);
            $table->dropColumn(['finalization_result', 'finalization_reason', 'finalization_date', 'finalized_by']);
        });
    }
};
