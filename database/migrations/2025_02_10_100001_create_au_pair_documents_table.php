<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('au_pair_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('au_pair_process_id')->constrained()->onDelete('cascade');

            $table->string('document_type'); // cedula, passport, drivers_license, profile_photo, enrollment_form, psych_test, child_photos, etc.
            $table->enum('stage', ['admission', 'application_payment1', 'application_payment2', 'visa'])->default('admission');
            $table->enum('uploaded_by_type', ['participant', 'staff'])->default('participant');

            $table->string('file_path');
            $table->string('original_filename');
            $table->unsignedInteger('file_size')->default(0); // bytes

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->dateTime('reviewed_at')->nullable();

            $table->boolean('is_required')->default(true);
            $table->unsignedTinyInteger('min_count')->default(1); // for refs: character_ref=2, childcare_ref=3
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('au_pair_process_id');
            $table->index('document_type');
            $table->index('stage');
            $table->index('status');
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('au_pair_documents');
    }
};
