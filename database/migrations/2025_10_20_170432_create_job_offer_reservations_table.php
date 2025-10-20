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
        Schema::create('job_offer_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('job_offer_id')->constrained()->onDelete('cascade');
            $table->foreignId('application_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('reservation_fee', 10, 2)->default(800.00)->comment('Tarifa de reserva USD');
            $table->decimal('cancellation_fee', 10, 2)->default(100.00)->comment('Penalidad por cancelación USD');
            $table->enum('status', ['reserved', 'confirmed', 'cancelled', 'completed'])->default('reserved');
            $table->timestamp('reserved_at');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('cancellation_reason')->nullable();
            $table->boolean('fee_paid')->default(false);
            $table->boolean('refund_processed')->default(false);
            $table->timestamps();
            
            // Índices
            $table->index('user_id');
            $table->index('job_offer_id');
            $table->index('application_id');
            $table->index('status');
            $table->index('reserved_at');
            
            // Constraint: un usuario solo puede tener una reserva activa a la vez
            $table->unique(['user_id', 'job_offer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_offer_reservations');
    }
};
