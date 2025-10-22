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
        Schema::create('au_pair_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('application_id')->nullable()->constrained()->onDelete('cascade');
            
            // Fotos y video
            $table->json('photos')->nullable(); // Array de paths
            $table->string('main_photo')->nullable();
            $table->string('video_presentation')->nullable();
            $table->integer('video_duration')->nullable(); // segundos
            
            // Carta a familia
            $table->text('dear_family_letter')->nullable();
            
            // Preferencias de familia
            $table->string('preferred_ages')->nullable(); // "0-2,3-5"
            $table->integer('max_children')->default(3);
            $table->text('ideal_family_description')->nullable();
            
            // Estado del perfil
            $table->enum('profile_status', ['draft', 'pending', 'active', 'matched', 'inactive'])->default('draft');
            $table->boolean('profile_complete')->default(false);
            $table->integer('profile_views')->default(0);
            
            // Disponibilidad
            $table->date('available_from')->nullable();
            $table->integer('commitment_months')->default(12);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('profile_status');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('au_pair_profiles');
    }
};
