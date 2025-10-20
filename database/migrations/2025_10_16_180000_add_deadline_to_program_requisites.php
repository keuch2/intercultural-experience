<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Agregar campo deadline a requisitos de programa
     * Épica 5 - Sistema de Deadlines
     */
    public function up(): void
    {
        Schema::table('program_requisites', function (Blueprint $table) {
            $table->date('deadline')->nullable()->after('payment_amount')
                ->comment('Fecha límite para completar el requisito');
            $table->boolean('send_reminders')->default(true)->after('deadline')
                ->comment('Enviar recordatorios automáticos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_requisites', function (Blueprint $table) {
            $table->dropColumn(['deadline', 'send_reminders']);
        });
    }
};
