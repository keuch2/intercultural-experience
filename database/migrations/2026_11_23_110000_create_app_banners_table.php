<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Banners de la app gestionados desde el admin (pedido de IE, nov 2026): imagen o texto, en el Inicio
 * público y/o en el Inicio del participante. Reemplaza el banner fijo "Postulá como Au Pair" de la app,
 * que se recrea acá como banner editable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_banners', function (Blueprint $table) {
            $table->id();
            $table->string('type', 10)->default('text'); // image | text
            $table->string('title')->nullable();
            $table->text('subtitle')->nullable();
            $table->string('badge', 40)->nullable();
            $table->string('image_path')->nullable();
            $table->string('background_color', 9)->default('#E52224');
            $table->string('button_label', 40)->nullable();
            $table->string('link_type', 10)->default('none'); // none | program | url
            $table->foreignId('link_program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->string('link_url', 500)->nullable();
            $table->string('audience', 15)->default('all'); // public | participants | all
            // Solo participantes con una postulación (visible) a este programa.
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['is_active', 'audience', 'sort_order']);
        });

        $auPair = DB::table('programs')->where('subcategory', 'Au Pair')->where('is_active', true)
            ->where('is_available_in_app', true)->orderBy('id')->value('id');
        if ($auPair) {
            DB::table('app_banners')->insert([
                'type' => 'text',
                'badge' => 'POSTULACIÓN ABIERTA',
                'title' => 'Postulá como Au Pair',
                'subtitle' => 'Viajá a Estados Unidos, viví con una familia anfitriona y viví un año inolvidable.',
                'background_color' => '#E52224',
                'button_label' => 'Conocé más',
                'link_type' => 'program',
                'link_program_id' => $auPair,
                'audience' => 'public',
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('app_banners');
    }
};
