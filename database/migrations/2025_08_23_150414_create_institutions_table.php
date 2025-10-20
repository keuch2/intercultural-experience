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
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // IE, YFU
            $table->string('name'); // Intercultural Experience, Youth For Understanding
            $table->string('short_name')->nullable(); // IE, YFU Paraguay
            $table->text('description')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('primary_color', 7)->default('#007bff'); // Hex color
            $table->string('secondary_color', 7)->default('#6c757d'); // Hex color
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('address')->nullable();
            $table->string('website_url')->nullable();
            $table->json('settings')->nullable(); // JSON for custom settings
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};
