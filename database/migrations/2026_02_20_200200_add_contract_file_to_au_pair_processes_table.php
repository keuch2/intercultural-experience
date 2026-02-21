<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('au_pair_processes', function (Blueprint $table) {
            $table->string('contract_file_path')->nullable()->after('contract_confirmed_by');
            $table->string('contract_original_filename')->nullable()->after('contract_file_path');
        });
    }

    public function down(): void
    {
        Schema::table('au_pair_processes', function (Blueprint $table) {
            $table->dropColumn(['contract_file_path', 'contract_original_filename']);
        });
    }
};
