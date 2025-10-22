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
        Schema::create('au_pair_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('au_pair_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('family_profile_id')->constrained()->onDelete('cascade');
            
            $table->enum('au_pair_status', ['pending', 'interested', 'not_interested'])->default('pending');
            $table->enum('family_status', ['pending', 'interested', 'not_interested'])->default('pending');
            $table->boolean('is_matched')->default(false);
            $table->datetime('matched_at')->nullable();
            
            // Interacciones
            $table->integer('messages_count')->default(0);
            $table->datetime('last_interaction')->nullable();
            $table->integer('video_calls_count')->default(0);
            
            $table->timestamps();
            
            $table->unique(['au_pair_profile_id', 'family_profile_id']);
            $table->index('is_matched');
            $table->index(['au_pair_status', 'family_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('au_pair_matches');
    }
};
