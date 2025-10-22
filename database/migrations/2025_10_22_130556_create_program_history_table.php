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
        Schema::create('program_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->foreignId('program_id')->constrained()->onDelete('cascade');
            
            $table->string('program_name'); // Nombre del programa al momento de completarlo
            $table->string('program_category');
            
            $table->date('started_at')->nullable();
            $table->date('completed_at')->nullable();
            $table->enum('completion_status', ['completed', 'withdrawn', 'terminated'])->default('completed');
            
            $table->text('completion_notes')->nullable();
            $table->decimal('final_cost', 10, 2)->nullable();
            $table->decimal('final_payment', 10, 2)->nullable();
            
            // Certificado/Diploma
            $table->string('certificate_path')->nullable();
            $table->date('certificate_issued_at')->nullable();
            
            // IE Cue (Alumni)
            $table->boolean('is_ie_cue')->default(true);
            $table->integer('satisfaction_rating')->nullable(); // 1-5
            $table->text('testimonial')->nullable();
            
            $table->timestamps();
            
            $table->index(['user_id', 'is_ie_cue']);
            $table->index('program_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_history');
    }
};
