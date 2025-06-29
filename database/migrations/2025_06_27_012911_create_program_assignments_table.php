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
        Schema::create('program_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('program_id')->constrained()->onDelete('cascade');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade'); // Admin que asignó
            $table->enum('status', [
                'assigned',      // Asignado, esperando aplicación
                'applied',       // Usuario aplicó
                'under_review',  // En revisión
                'accepted',      // Aceptado en el programa
                'rejected',      // Rechazado
                'completed',     // Programa completado
                'cancelled'      // Cancelado
            ])->default('assigned');
            $table->text('assignment_notes')->nullable(); // Notas del admin al asignar
            $table->text('admin_notes')->nullable(); // Notas administrativas
            $table->datetime('assigned_at');
            $table->datetime('applied_at')->nullable();
            $table->datetime('reviewed_at')->nullable();
            $table->datetime('accepted_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->json('program_data')->nullable(); // Datos específicos del programa para el usuario
            $table->boolean('can_apply')->default(true); // Si puede aplicar
            $table->boolean('is_priority')->default(false); // Asignación prioritaria
            $table->date('application_deadline')->nullable(); // Fecha límite para aplicar
            $table->timestamps();
            
            // Índices
            $table->unique(['user_id', 'program_id']); // Un usuario solo puede tener una asignación por programa
            $table->index(['status', 'assigned_at']);
            $table->index(['user_id', 'status']);
            $table->index(['program_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_assignments');
    }
};
