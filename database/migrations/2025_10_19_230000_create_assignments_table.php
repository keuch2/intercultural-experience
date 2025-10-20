<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabla para gestionar asignaciones de programas a participantes por parte de agentes
     */
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')
                ->comment('Participante al que se asigna el programa');
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade')
                ->comment('Programa asignado');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade')
                ->comment('Agente que realizó la asignación');
            $table->foreignId('application_id')->nullable()->constrained('applications')->onDelete('set null')
                ->comment('Aplicación creada cuando el participante aplica');
            
            // Estados de la asignación
            $table->enum('status', [
                'assigned',      // Asignado, pendiente de aplicar
                'applied',       // Participante aplicó al programa
                'under_review',  // En revisión por admin
                'accepted',      // Aceptado
                'rejected',      // Rechazado
                'completed',     // Completado
                'cancelled'      // Cancelado
            ])->default('assigned')->comment('Estado de la asignación');
            
            // Fechas importantes
            $table->timestamp('assigned_at')->useCurrent()
                ->comment('Fecha de asignación');
            $table->timestamp('applied_at')->nullable()
                ->comment('Fecha en que el participante aplicó');
            $table->date('application_deadline')->nullable()
                ->comment('Fecha límite para aplicar');
            
            // Información adicional
            $table->text('assignment_notes')->nullable()
                ->comment('Notas del agente al asignar');
            $table->text('admin_notes')->nullable()
                ->comment('Notas del administrador');
            $table->boolean('is_priority')->default(false)
                ->comment('Asignación prioritaria');
            
            $table->timestamps();
            
            // Índices para optimizar búsquedas
            $table->index(['user_id', 'status']);
            $table->index(['program_id', 'status']);
            $table->index(['assigned_by', 'created_at']);
            $table->index('application_deadline');
            
            // Constraint: Un usuario no puede tener múltiples asignaciones activas del mismo programa
            $table->unique(['user_id', 'program_id', 'status'], 'unique_active_assignment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
