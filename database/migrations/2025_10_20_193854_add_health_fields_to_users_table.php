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
        Schema::table('users', function (Blueprint $table) {
            $table->text('medical_conditions')->nullable()->after('avatar');
            $table->text('allergies')->nullable()->after('medical_conditions');
            $table->text('medications')->nullable()->after('allergies');
            $table->string('health_insurance', 100)->nullable()->after('medications');
            $table->string('health_insurance_number', 100)->nullable()->after('health_insurance');
            $table->enum('blood_type', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])->nullable()->after('health_insurance_number');
            $table->string('emergency_medical_contact', 100)->nullable()->after('blood_type');
            $table->string('emergency_medical_phone', 50)->nullable()->after('emergency_medical_contact');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'medical_conditions',
                'allergies',
                'medications',
                'health_insurance',
                'health_insurance_number',
                'blood_type',
                'emergency_medical_contact',
                'emergency_medical_phone'
            ]);
        });
    }
};
