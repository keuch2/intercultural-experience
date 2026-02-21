<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('au_pair_support_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('au_pair_process_id')->constrained()->onDelete('cascade');
            $table->enum('log_type', ['arrival_followup', 'monthly_followup', 'incident', 'experience_evaluation'])->default('monthly_followup');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('log_date');
            $table->unsignedSmallInteger('follow_up_number')->nullable();
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->nullable();
            $table->text('resolution')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->unsignedBigInteger('logged_by')->nullable();
            $table->timestamps();

            $table->index('au_pair_process_id');
            $table->foreign('logged_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('au_pair_support_logs');
    }
};
