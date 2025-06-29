<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_form_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('application_id')->nullable()->constrained()->onDelete('cascade');
            $table->json('form_data'); // Datos del formulario en JSON
            $table->enum('status', ['draft', 'submitted', 'reviewed', 'approved', 'rejected'])->default('draft');
            $table->text('signature_data')->nullable(); // Datos de la firma digital
            $table->text('parent_signature_data')->nullable(); // Firma de los padres
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_notes')->nullable();
            $table->json('validation_errors')->nullable(); // Errores de validación
            $table->string('form_version', 10)->nullable(); // Versión del formulario usado
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['program_form_id', 'status']);
            $table->index(['application_id']);
            $table->unique(['program_form_id', 'user_id']); // Un usuario, una respuesta por formulario
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
