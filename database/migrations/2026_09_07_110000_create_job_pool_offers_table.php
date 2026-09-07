<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pool de Ofertas Laborales (spec W&T): la oferta se define con empleador, estado,
 * ciudad, posiciones y PDF; todo el detalle del puesto vive en el PDF.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_pool_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->string('employer_name');
            $table->string('state', 100);
            $table->string('city', 100);
            $table->unsignedSmallInteger('positions_total');
            $table->unsignedSmallInteger('positions_available');
            $table->string('pdf_path')->nullable();
            $table->string('pdf_original_filename')->nullable();
            $table->enum('status', ['active', 'paused', 'closed'])->default('active');
            $table->dateTime('published_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['program_id', 'status', 'positions_available'], 'job_pool_offers_visibility_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_pool_offers');
    }
};
