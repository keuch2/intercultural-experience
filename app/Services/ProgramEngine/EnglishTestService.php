<?php

namespace App\Services\ProgramEngine;

use App\Models\ProgramEnglishTest;
use App\Models\ProgramProcess;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;

class EnglishTestService
{
    public function record(ProgramProcess $process, array $data, ?UploadedFile $pdf = null, ?User $actor = null): ProgramEnglishTest
    {
        if ($this->remainingAttempts($process) <= 0) {
            throw new InvalidArgumentException('Se alcanzó el máximo de intentos de test de inglés.');
        }

        $attempt = (int) $process->englishTests()->max('attempt_number') + 1;
        $final = isset($data['final_score']) ? (int) $data['final_score'] : null;

        $test = new ProgramEnglishTest(array_merge($data, [
            'program_process_id' => $process->id,
            'attempt_number' => $attempt,
            'cefr_level' => $data['cefr_level'] ?? ProgramEnglishTest::scoreToLevel($final),
            'recorded_by' => $actor?->id,
        ]));

        if ($pdf) {
            $test->test_pdf_path = $pdf->store("program-docs/{$process->program?->slug}/{$process->id}/english", 'public');
        }
        $test->save();

        return $test;
    }

    public function remainingAttempts(ProgramProcess $process): int
    {
        $max = ProgramDefinition::for($process->program)->maxEnglishAttempts();

        return max(0, $max - $process->englishTests()->count());
    }

    public function bestLevel(ProgramProcess $process): ?string
    {
        $levels = $process->englishTests()->pluck('cefr_level')->filter()->all();
        if (! $levels) {
            return null;
        }
        usort($levels, fn ($a, $b) => array_search($a, ProgramEnglishTest::CEFR_ORDER, true) <=> array_search($b, ProgramEnglishTest::CEFR_ORDER, true));

        return end($levels) ?: null;
    }

    public function meetsMinimum(ProgramProcess $process): bool
    {
        $min = ProgramDefinition::for($process->program)->minEnglishLevel();

        return ProgramEnglishTest::levelMeets($this->bestLevel($process), $min);
    }
}
