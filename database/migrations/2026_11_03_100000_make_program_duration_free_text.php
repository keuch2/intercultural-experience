<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const UNITS = [
        'days' => ['día', 'días'],
        'weeks' => ['semana', 'semanas'],
        'months' => ['mes', 'meses'],
        'years' => ['año', 'años'],
    ];

    /**
     * programs.duration pasa a ser texto libre informativo ("4 a 6 meses", "Variable").
     * Los valores numéricos existentes (número + duration_unit) se convierten a texto y se elimina duration_unit.
     */
    public function up(): void
    {
        if (Schema::hasColumn('programs', 'duration_unit')) {
            DB::table('programs')->whereNotNull('duration')->get(['id', 'duration', 'duration_unit'])
                ->filter(fn ($p) => is_numeric(trim((string) $p->duration)))
                ->each(fn ($p) => DB::table('programs')->where('id', $p->id)->update([
                    'duration' => $this->label((int) $p->duration, $p->duration_unit ?: 'days'),
                ]));

            Schema::table('programs', function (Blueprint $table) {
                $table->dropColumn('duration_unit');
            });
        }
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->string('duration_unit', 10)->default('days')->after('duration');
        });
    }

    private function label(int $value, string $unit): ?string
    {
        if ($value <= 0) {
            return null;
        }
        $unit = array_key_exists($unit, self::UNITS) ? $unit : 'days';
        $plural = fn (int $n, string $u) => $n.' '.self::UNITS[$u][$n === 1 ? 0 : 1];

        if ($unit === 'years') {
            return $plural($value, 'years');
        }
        if ($unit === 'months') {
            $months = $value;
        } else {
            $days = $unit === 'weeks' ? $value * 7 : $value;
            if ($days < 28) {
                return $plural($value, $unit);
            }
            $months = max(1, (int) round($days / 30.4375));
        }

        $years = intdiv($months, 12);
        $months %= 12;

        return collect([$years ? $plural($years, 'years') : null, $months ? $plural($months, 'months') : null])
            ->filter()->implode(' y ');
    }
};
