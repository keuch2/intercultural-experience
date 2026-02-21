<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('au_pair_english_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('au_pair_process_id')->constrained()->onDelete('cascade');
            $table->foreignId('english_evaluation_id')->nullable()->constrained()->nullOnDelete();

            $table->string('evaluator_name')->nullable();
            $table->string('exam_name')->nullable();
            $table->unsignedSmallInteger('oral_score')->nullable();
            $table->unsignedSmallInteger('listening_score')->nullable();
            $table->unsignedSmallInteger('reading_score')->nullable();
            $table->unsignedSmallInteger('final_score')->nullable();
            $table->string('cefr_level', 10)->nullable();
            $table->text('observations')->nullable();
            $table->string('test_pdf_path')->nullable();
            $table->boolean('results_sent_to_applicant')->default(false);
            $table->dateTime('results_sent_at')->nullable();
            $table->unsignedTinyInteger('attempt_number')->default(1);

            $table->timestamps();

            $table->index('au_pair_process_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('au_pair_english_tests');
    }
};
