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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('log_name')->nullable(); // general, auth, admin, etc.
            $table->text('description');
            $table->string('subject_type')->nullable(); // Model class name
            $table->unsignedBigInteger('subject_id')->nullable(); // Model ID
            $table->string('causer_type')->nullable(); // User model class
            $table->unsignedBigInteger('causer_id')->nullable(); // User ID
            $table->json('properties')->nullable(); // Additional data
            $table->json('changes')->nullable(); // Before/after values
            $table->string('action')->nullable(); // created, updated, deleted, etc.
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('method', 10)->nullable(); // GET, POST, PUT, DELETE
            $table->text('url')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index(['subject_type', 'subject_id']);
            $table->index(['causer_type', 'causer_id']);
            $table->index(['log_name', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
