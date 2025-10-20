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
        Schema::create('job_offers', function (Blueprint $table) {
            $table->id();
            $table->string('job_offer_id', 50)->unique()->comment('ID único de la oferta');
            $table->foreignId('sponsor_id')->constrained()->onDelete('cascade');
            $table->foreignId('host_company_id')->constrained()->onDelete('cascade');
            $table->string('position')->comment('Posición/cargo ofrecido');
            $table->text('description')->nullable();
            $table->string('city', 100);
            $table->string('state', 100);
            $table->decimal('salary_min', 10, 2)->comment('Salario mínimo por hora USD');
            $table->decimal('salary_max', 10, 2)->comment('Salario máximo por hora USD');
            $table->integer('hours_per_week')->default(40);
            $table->enum('housing_type', ['provided', 'assisted', 'not_provided'])->default('not_provided');
            $table->decimal('housing_cost', 10, 2)->nullable()->comment('Costo de vivienda si aplica');
            $table->integer('total_slots')->comment('Total de cupos disponibles');
            $table->integer('available_slots')->comment('Cupos disponibles actualmente');
            $table->enum('required_english_level', ['A2', 'B1', 'B1+', 'B2', 'C1', 'C2'])->default('B1');
            $table->enum('required_gender', ['male', 'female', 'any'])->default('any');
            $table->date('start_date')->comment('Fecha de inicio del trabajo');
            $table->date('end_date')->comment('Fecha de fin del trabajo');
            $table->enum('status', ['available', 'full', 'cancelled'])->default('available');
            $table->text('requirements')->nullable()->comment('Requisitos adicionales');
            $table->text('benefits')->nullable()->comment('Beneficios ofrecidos');
            $table->timestamps();
            
            // Índices
            $table->index('job_offer_id');
            $table->index('sponsor_id');
            $table->index('host_company_id');
            $table->index('city');
            $table->index('state');
            $table->index('status');
            $table->index('required_english_level');
            $table->index('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_offers');
    }
};
