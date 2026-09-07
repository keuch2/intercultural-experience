<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Instancias de documentos subidos (clon de au_pair_documents referenciando la
 * configuración por requirement_key).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_process_id')->constrained('program_processes')->cascadeOnDelete();
            $table->string('requirement_key', 60);
            $table->string('stage_key', 50);
            $table->enum('uploaded_by_type', ['participant', 'staff'])->default('participant');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('file_path');
            $table->string('original_filename');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('mime_type', 120)->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();

            $table->text('notes')->nullable();
            $table->text('deletion_reason')->nullable();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['program_process_id', 'requirement_key']);
            $table->index(['program_process_id', 'stage_key']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_documents');
    }
};
