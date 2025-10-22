<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Eliminar "USA" de los nombres de programas
        DB::table('programs')
            ->where('name', 'Work & Travel USA')
            ->update(['name' => 'Work & Travel']);
        
        DB::table('programs')
            ->where('name', 'Au Pair USA')
            ->update(['name' => 'Au Pair']);
        
        DB::table('programs')
            ->where('name', 'Intern & Trainee USA')
            ->update(['name' => 'Intern & Trainee']);
        
        // Eliminar programa "Super Programa"
        DB::table('programs')
            ->where('name', 'Super Programa')
            ->delete();
        
        // También eliminar cualquier variante
        DB::table('programs')
            ->where('name', 'LIKE', '%Super Programa%')
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurar nombres con USA
        DB::table('programs')
            ->where('name', 'Work & Travel')
            ->update(['name' => 'Work & Travel USA']);
        
        DB::table('programs')
            ->where('name', 'Au Pair')
            ->update(['name' => 'Au Pair USA']);
        
        DB::table('programs')
            ->where('name', 'Intern & Trainee')
            ->update(['name' => 'Intern & Trainee USA']);
    }
};
