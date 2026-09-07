<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_support_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_process_id')->constrained('program_processes')->cascadeOnDelete();
            // string (no enum): los tipos permitidos vienen de programs.rules.support_log_types
            $table->string('log_type', 40)->default('program_followup');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('log_date');
            $table->unsignedSmallInteger('follow_up_number')->nullable();
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->nullable();
            $table->text('resolution')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->foreignId('logged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['program_process_id', 'log_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_support_logs');
    }
};
