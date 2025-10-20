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
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique(); // OFFICE, TRAVEL, MARKETING, etc.
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#6c757d'); // Color hex para UI
            $table->decimal('budget_limit', 15, 2)->nullable(); // Límite presupuestario
            $table->boolean('requires_approval')->default(false); // Si requiere aprobación
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_categories');
    }
};
