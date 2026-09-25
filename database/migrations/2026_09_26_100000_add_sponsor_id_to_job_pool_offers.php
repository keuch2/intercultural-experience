<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_pool_offers', function (Blueprint $table) {
            // Sponsor de la oferta (opcional); el Job Placement lo hereda al seleccionar la oferta
            $table->foreignId('sponsor_id')->nullable()->after('program_id')->constrained('sponsors')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('job_pool_offers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sponsor_id');
        });
    }
};
