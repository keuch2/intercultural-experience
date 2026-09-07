<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_process_checklist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_process_id')->constrained('program_processes')->cascadeOnDelete();
            $table->string('item_key', 50);
            $table->boolean('is_done')->default(false);
            $table->dateTime('done_at')->nullable();
            $table->foreignId('done_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('file_path')->nullable();
            $table->string('original_filename')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['program_process_id', 'item_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_process_checklist');
    }
};
