<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Asignación oferta ↔ participante. `active_process_id` replica program_process_id
 * mientras status='active' y pasa a NULL al liberar: su índice UNIQUE garantiza a
 * nivel DB "una sola asignación activa por participante" (MySQL no tiene índices
 * parciales).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_pool_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_pool_offer_id')->constrained('job_pool_offers')->cascadeOnDelete();
            $table->foreignId('program_process_id')->constrained('program_processes')->cascadeOnDelete();
            $table->unsignedBigInteger('active_process_id')->nullable()->unique();
            $table->enum('status', ['active', 'released', 'reassigned'])->default('active');
            $table->dateTime('selected_at');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete(); // null = auto-selección del participante
            $table->dateTime('released_at')->nullable();
            $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('release_reason')->nullable();
            $table->timestamps();

            $table->index(['job_pool_offer_id', 'status']);
            $table->index('program_process_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_pool_assignments');
    }
};
