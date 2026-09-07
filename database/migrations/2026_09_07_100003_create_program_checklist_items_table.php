<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->string('stage_key', 50)->nullable();
            $table->string('key', 50);
            $table->string('label');
            $table->enum('item_type', ['boolean', 'file'])->default('boolean');
            $table->boolean('required_for_advance')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['program_id', 'key']);
            $table->index(['program_id', 'stage_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_checklist_items');
    }
};
