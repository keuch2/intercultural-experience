<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_pool_offers', function (Blueprint $table) {
            // Flyer / imagen de la oferta (disco público) visible en la tarjeta de la app
            $table->string('image_path')->nullable()->after('pdf_original_filename');
            $table->string('image_original_filename')->nullable()->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('job_pool_offers', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'image_original_filename']);
        });
    }
};
