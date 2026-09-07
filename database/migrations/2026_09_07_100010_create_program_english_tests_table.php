<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_english_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_process_id')->constrained('program_processes')->cascadeOnDelete();
            $table->foreignId('english_evaluation_id')->nullable()->constrained()->nullOnDelete();

            $table->string('evaluator_name')->nullable();
            $table->string('exam_name')->nullable();
            $table->string('oral_score', 20)->nullable(); // Good / Great / Excellent
            $table->unsignedSmallInteger('listening_score')->nullable();
            $table->unsignedSmallInteger('reading_score')->nullable();
            $table->unsignedSmallInteger('final_score')->nullable();
            $table->string('cefr_level', 10)->nullable();
            $table->text('observations')->nullable();
            $table->string('test_pdf_path')->nullable();
            $table->boolean('results_sent_to_applicant')->default(false);
            $table->dateTime('results_sent_at')->nullable();
            $table->unsignedTinyInteger('attempt_number')->default(1);
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index('program_process_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_english_tests');
    }
};
