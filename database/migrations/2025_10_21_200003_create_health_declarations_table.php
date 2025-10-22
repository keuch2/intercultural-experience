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
        Schema::create('health_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('has_diseases')->default(false);
            $table->text('diseases_detail')->nullable();
            $table->boolean('has_allergies')->default(false);
            $table->text('allergies_detail')->nullable();
            $table->boolean('has_dietary_restrictions')->default(false);
            $table->text('dietary_restrictions_detail')->nullable();
            $table->boolean('has_learning_disabilities')->default(false);
            $table->text('learning_disabilities_detail')->nullable();
            $table->boolean('has_physical_limitations')->default(false);
            $table->text('physical_limitations_detail')->nullable();
            $table->boolean('under_medical_treatment')->default(false);
            $table->text('medical_treatment_detail')->nullable();
            $table->boolean('takes_medication')->default(false);
            $table->text('medication_detail')->nullable();
            $table->boolean('can_lift_25_pounds')->default(true); // Au Pair requirement
            $table->boolean('allergic_to_pets')->default(false);
            $table->text('pet_allergies_detail')->nullable();
            $table->date('declaration_date');
            $table->timestamps();
            
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_declarations');
    }
};
