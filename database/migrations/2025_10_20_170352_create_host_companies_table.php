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
        Schema::create('host_companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nombre de la empresa host');
            $table->string('industry', 100)->nullable()->comment('Industria/sector');
            $table->string('city', 100);
            $table->string('state', 100);
            $table->string('country', 100)->default('USA');
            $table->text('address')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->decimal('rating', 3, 2)->default(0)->comment('Rating promedio (0-5)');
            $table->integer('total_participants')->default(0)->comment('Total de participantes históricos');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index('city');
            $table->index('state');
            $table->index('industry');
            $table->index('is_active');
            $table->index('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('host_companies');
    }
};
