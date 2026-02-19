<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('au_pair_matches_extended', function (Blueprint $table) {
            $table->id();
            $table->foreignId('au_pair_process_id')->constrained()->onDelete('cascade');
            $table->enum('match_type', ['initial', 'rematch', 'extension'])->default('initial');
            $table->date('match_date')->nullable();
            $table->string('host_state')->nullable();
            $table->string('host_city')->nullable();
            $table->text('host_address')->nullable();
            $table->string('host_mom_name')->nullable();
            $table->string('host_dad_name')->nullable();
            $table->string('host_email')->nullable();
            $table->string('host_phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('ended_at')->nullable();
            $table->text('end_reason')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->timestamps();

            $table->index('au_pair_process_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('au_pair_matches_extended');
    }
};
