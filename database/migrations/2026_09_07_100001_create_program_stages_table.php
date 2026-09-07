<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->string('key', 50);
            $table->string('label');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_terminal')->default(false);
            $table->string('mobile_screen', 80)->nullable();
            // Guards declarativos evaluados por StageEvaluator:
            // {require_docs_approved, require_gates[], require_checklist[], require_english_min_level,
            //  require_job_assignment, require_placement_complete, manual_only}
            $table->json('guards')->nullable();
            $table->timestamps();

            $table->unique(['program_id', 'key']);
            $table->index(['program_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_stages');
    }
};
