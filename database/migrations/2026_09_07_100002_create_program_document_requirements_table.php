<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo de documentos requeridos por programa. Absorbe la forma de
 * AuPairDocument::documentTypes() (label, stage, required, sort, min_count,
 * allow_multiple, uploaded_by, section) como filas configurables.
 *
 * Las referencias son por `key` (no FK) para que renombrar/desactivar la
 * configuración nunca cascadee sobre los archivos ya subidos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_document_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->string('stage_key', 50);
            $table->string('group_key', 50)->nullable(); // tab visual; default = stage_key
            $table->string('key', 60);
            $table->string('label');
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(true);
            $table->unsignedTinyInteger('min_count')->default(1);
            $table->boolean('allow_multiple')->default(false);
            $table->enum('uploaded_by', ['participant', 'staff'])->default('participant');
            $table->string('unlock_gate_key', 50)->nullable(); // habilitado al verificar este gate de pago
            $table->string('section', 20)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['program_id', 'key']);
            $table->index(['program_id', 'stage_key', 'group_key'], 'prog_doc_req_program_stage_group_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_document_requirements');
    }
};
