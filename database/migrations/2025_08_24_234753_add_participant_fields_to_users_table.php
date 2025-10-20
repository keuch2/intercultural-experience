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
            $table->string('city')->nullable()->after('address');
            $table->string('country')->nullable()->after('city');
            $table->enum('academic_level', ['bachiller', 'licenciatura', 'maestria', 'posgrado', 'doctorado'])->nullable()->after('country');
            $table->enum('english_level', ['basico', 'intermedio', 'avanzado', 'nativo'])->nullable()->after('academic_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['city', 'country', 'academic_level', 'english_level']);
        });
    }
};
