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
        Schema::create('childcare_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('experience_type', ['babysitter', 'teacher', 'family', 'daycare', 'camp', 'other']);
            $table->string('ages_cared')->nullable(); // "0-2", "3-5", "6-10", etc
            $table->integer('duration_months');
            $table->text('responsibilities');
            $table->boolean('cared_for_infants')->default(false);
            $table->boolean('special_needs_experience')->default(false);
            $table->text('special_needs_detail')->nullable();
            $table->string('reference_name')->nullable();
            $table->string('reference_phone')->nullable();
            $table->string('reference_email')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('childcare_experiences');
    }
};
