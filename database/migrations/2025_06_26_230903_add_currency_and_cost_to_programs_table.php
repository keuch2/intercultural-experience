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
        Schema::table('programs', function (Blueprint $table) {
            // Campos que estaban en el frontend pero no en la DB
            $table->string('location')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('application_deadline')->nullable();
            $table->string('duration')->nullable();
            $table->integer('credits')->nullable();
            $table->integer('capacity')->default(0);
            $table->integer('available_spots')->nullable();
            $table->string('image_url')->nullable();
            
            // Nuevos campos para sistema de monedas
            $table->decimal('cost', 15, 4)->default(0);
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);
            $table->dropColumn([
                'location', 'start_date', 'end_date', 'application_deadline',
                'duration', 'credits', 'capacity', 'available_spots', 
                'image_url', 'cost', 'currency_id'
            ]);
        });
    }
};
