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
        Schema::create('job_fair_events', function (Blueprint $table) {
            $table->id();
            
            // Información del evento
            $table->string('event_name');
            $table->text('description')->nullable();
            $table->date('event_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('event_type', ['virtual', 'presencial', 'hybrid']);
            
            // Ubicación (para eventos presenciales)
            $table->string('venue_name')->nullable();
            $table->string('venue_address')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->string('country')->default('USA');
            
            // Información virtual (para eventos online)
            $table->string('virtual_platform')->nullable(); // Zoom, Teams, etc.
            $table->string('meeting_link')->nullable();
            $table->string('meeting_id')->nullable();
            $table->string('meeting_password')->nullable();
            
            // Capacidad y registro
            $table->integer('max_participants')->nullable();
            $table->integer('registered_participants')->default(0);
            $table->integer('max_schools')->nullable();
            $table->integer('registered_schools')->default(0);
            $table->datetime('registration_opens');
            $table->datetime('registration_closes');
            
            // Estado del evento
            $table->enum('status', ['draft', 'published', 'registration_open', 'registration_closed', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->text('cancellation_reason')->nullable();
            
            // Configuración
            $table->boolean('requires_mec_validation')->default(true);
            $table->boolean('requires_payment')->default(false);
            $table->decimal('registration_fee', 8, 2)->nullable();
            $table->json('required_documents')->nullable(); // Array de documentos requeridos
            
            // Estadísticas post-evento
            $table->integer('total_interviews')->default(0);
            $table->integer('total_offers')->default(0);
            $table->integer('successful_placements')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['event_date', 'status']);
            $table->index('event_type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_fair_events');
    }
};
