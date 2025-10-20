<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Agregar campo available_slots a tabla programs
     */
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->integer('available_slots')->default(0)->after('cost')
                ->comment('Cupos disponibles en el programa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn('available_slots');
        });
    }
};
