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
        Schema::create('teacher_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('certification_name');
            $table->string('issuing_institution');
            $table->date('issue_date');
            $table->date('expiry_date')->nullable();
            $table->string('certification_number')->nullable();
            $table->string('document_path')->nullable();
            $table->boolean('is_apostilled')->default(false);
            $table->boolean('verified')->default(false);
            $table->date('verification_date')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_certifications');
    }
};
