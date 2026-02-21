<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('au_pair_documents', function (Blueprint $table) {
            $table->string('deletion_reason')->nullable()->after('rejection_reason');
            $table->unsignedBigInteger('deleted_by')->nullable()->after('deletion_reason');
        });
    }

    public function down(): void
    {
        Schema::table('au_pair_documents', function (Blueprint $table) {
            $table->dropColumn(['deletion_reason', 'deleted_by']);
        });
    }
};
