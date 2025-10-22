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
        Schema::create('family_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('family_name');
            $table->string('parent1_name');
            $table->string('parent2_name')->nullable();
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('city');
            $table->string('state', 2);
            $table->string('country')->default('USA');
            
            // Niños
            $table->integer('number_of_children');
            $table->json('children_ages'); // [2, 5, 8]
            $table->boolean('has_infants')->default(false);
            $table->boolean('has_special_needs')->default(false);
            $table->text('special_needs_detail')->nullable();
            
            // Casa y mascotas
            $table->boolean('has_pets')->default(false);
            $table->string('pet_types')->nullable();
            $table->boolean('smoking_household')->default(false);
            
            // Requisitos
            $table->enum('required_gender', ['female', 'male', 'any'])->default('any');
            $table->boolean('drivers_license_required')->default(false);
            $table->boolean('swimming_required')->default(false);
            
            // Oferta económica
            $table->decimal('weekly_stipend', 8, 2)->default(195.75);
            $table->decimal('education_fund', 8, 2)->default(500);
            $table->text('additional_benefits')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('state');
            $table->index('has_infants');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_profiles');
    }
};
