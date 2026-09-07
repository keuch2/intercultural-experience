<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Historial del pool: por oferta y por participante (requisito de la spec). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_pool_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_pool_offer_id')->nullable()->constrained('job_pool_offers')->nullOnDelete();
            $table->foreignId('program_process_id')->nullable()->constrained('program_processes')->nullOnDelete();
            $table->string('event_type', 40); // offer_published, offer_updated, offer_pdf_replaced, offer_paused, offer_reactivated, offer_closed, offer_deleted, selected, released, reassigned, positions_exhausted
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('actor_type', ['participant', 'staff', 'system'])->default('system');
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['job_pool_offer_id', 'created_at']);
            $table->index(['program_process_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_pool_events');
    }
};
