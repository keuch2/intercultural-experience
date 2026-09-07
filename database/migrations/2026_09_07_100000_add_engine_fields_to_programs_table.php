<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Motor de programas: identidad estable (slug), disponibilidad en app como columna
 * real, y configuración por programa (módulos, reglas, onboarding).
 *
 * `subcategory` se conserva como taxonomía de display; `slug` es el identificador
 * que usa el motor y las rutas nuevas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->string('slug', 60)->nullable()->unique()->after('name');
            $table->boolean('is_available_in_app')->default(false)->after('is_active');
            $table->boolean('engine_enabled')->default(false)->after('is_available_in_app');
            $table->json('modules')->nullable()->after('engine_enabled');
            $table->json('rules')->nullable()->after('modules');
            $table->json('onboarding')->nullable()->after('rules');
        });

        // Reparar colisión histórica: Work & Study sembrado con subcategory 'Work and Travel'.
        DB::table('programs')
            ->where('name', 'Work & Study')
            ->where('subcategory', 'Work and Travel')
            ->update(['subcategory' => 'Work and Study']);

        // Backfill de slug (único) y de is_available_in_app (Au Pair era el único habilitado).
        $used = [];
        foreach (DB::table('programs')->orderBy('id')->get(['id', 'name', 'subcategory']) as $program) {
            $base = Str::slug($program->name) ?: 'programa';
            $slug = $base;
            $i = 2;
            while (in_array($slug, $used, true)) {
                $slug = $base.'-'.$i++;
            }
            $used[] = $slug;

            DB::table('programs')->where('id', $program->id)->update([
                'slug' => $slug,
                'is_available_in_app' => $program->subcategory === 'Au Pair',
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'is_available_in_app', 'engine_enabled', 'modules', 'rules', 'onboarding']);
        });
    }
};
