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
        // Modificar el enum de role para incluir 'agent'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('user', 'admin', 'agent') NOT NULL DEFAULT 'user'");
        
        // Agregar campo created_by_agent_id
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('created_by_agent_id')
                ->nullable()
                ->after('role')
                ->constrained('users')
                ->onDelete('set null')
                ->comment('ID del agente que creó este usuario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar campo created_by_agent_id
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['created_by_agent_id']);
            $table->dropColumn('created_by_agent_id');
        });
        
        // Revertir enum de role
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('user', 'admin') NOT NULL DEFAULT 'user'");
    }
};
