<?php

use App\Services\ProgramEngine\ProgramDefinition;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * La etapa Visa de Work & Travel deja de ser "solo manual": exige documentos aprobados
 * y visa aprobada (resultado de la entrevista) antes de pasar a Support.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->patch(function (array $guards) {
            unset($guards['manual_only']);
            $guards['require_docs_approved'] = true;
            $guards['require_visa_approved'] = true;

            return $guards;
        });
    }

    public function down(): void
    {
        $this->patch(function (array $guards) {
            unset($guards['require_visa_approved']);
            $guards['require_docs_approved'] = false;
            $guards['manual_only'] = true;

            return $guards;
        });
    }

    private function patch(callable $fn): void
    {
        $rows = DB::table('program_stages')
            ->join('programs', 'programs.id', '=', 'program_stages.program_id')
            ->where('programs.slug', 'work-travel')
            ->where('program_stages.key', 'visa')
            ->select('program_stages.id', 'program_stages.guards')
            ->get();

        foreach ($rows as $row) {
            $guards = json_decode($row->guards ?? '[]', true) ?: [];
            DB::table('program_stages')->where('id', $row->id)->update(['guards' => json_encode($fn($guards))]);
        }

        if (class_exists(ProgramDefinition::class)) {
            ProgramDefinition::forget();
        }
    }
};
