<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * N gates de pago por programa (reemplaza los booleanos payment_1/2_verified).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_payment_gates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->string('key', 50);
            $table->string('label');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->decimal('amount', 12, 2)->nullable();
            $table->foreignId('currency_id')->nullable()->constrained()->nullOnDelete();
            $table->string('concept_match')->nullable(); // p.ej. "inscripci" para auto-detectar pagos por concepto
            $table->boolean('auto_verify_from_payments')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['program_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_payment_gates');
    }
};
