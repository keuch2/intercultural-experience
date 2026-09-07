<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Orquestador genérico por postulación (equivalente de au_pair_processes).
 * Las etapas y sus estados viven en JSON para que un programa creado desde el
 * admin nunca requiera migración.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('current_stage_key', 50);
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            // {stage_key: {status: locked|pending|in_progress|approved|rejected, entered_at, completed_at}}
            $table->json('stage_states')->nullable();
            // {job_pool: {enabled, enabled_at, enabled_by, allow_reselect}}
            $table->json('module_access')->nullable();
            $table->string('season', 20)->nullable();
            $table->date('enrollment_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('finalization_result', 30)->nullable();
            $table->text('finalization_reason')->nullable();
            $table->date('finalization_date')->nullable();
            $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['program_id', 'current_stage_key']);
            $table->index(['program_id', 'season']);
            $table->index(['program_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_processes');
    }
};
