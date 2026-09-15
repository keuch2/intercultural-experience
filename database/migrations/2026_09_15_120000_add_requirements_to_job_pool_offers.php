<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_pool_offers', function (Blueprint $table) {
            // Requisitos del puesto (nivel de inglés, experiencia, edad, licencia, etc.); visibles en la app
            $table->text('requirements')->nullable()->after('job_title');
        });
    }

    public function down(): void
    {
        Schema::table('job_pool_offers', function (Blueprint $table) {
            $table->dropColumn('requirements');
        });
    }
};
