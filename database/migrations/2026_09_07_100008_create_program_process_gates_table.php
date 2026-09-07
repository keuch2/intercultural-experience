<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_process_gates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_process_id')->constrained('program_processes')->cascadeOnDelete();
            $table->string('gate_key', 50);
            $table->boolean('is_verified')->default(false);
            $table->dateTime('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['program_process_id', 'gate_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_process_gates');
    }
};
