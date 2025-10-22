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
        Schema::create('references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('reference_type', ['childcare', 'character', 'professional', 'academic']);
            $table->string('name');
            $table->string('relationship');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('organization')->nullable();
            $table->string('position')->nullable();
            $table->text('letter_content')->nullable();
            $table->string('letter_file_path')->nullable();
            $table->boolean('verified')->default(false);
            $table->date('verification_date')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'reference_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('references');
    }
};
