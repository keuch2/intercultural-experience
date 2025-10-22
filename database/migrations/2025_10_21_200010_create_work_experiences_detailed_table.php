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
        Schema::create('work_experiences_detailed', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('company_name');
            $table->string('position');
            $table->string('department')->nullable();
            $table->text('responsibilities');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            
            // Para Teachers
            $table->enum('institution_type', ['public', 'private', 'charter', 'other'])->nullable();
            $table->string('grade_levels')->nullable(); // "K-5", "6-8", "9-12"
            $table->json('subjects_taught')->nullable();
            $table->integer('weekly_hours')->nullable();
            
            // Referencias laborales
            $table->string('supervisor_name')->nullable();
            $table->string('supervisor_phone')->nullable();
            $table->string('supervisor_email')->nullable();
            
            $table->timestamps();
            
            $table->index(['user_id', 'is_current']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_experiences_detailed');
    }
};
