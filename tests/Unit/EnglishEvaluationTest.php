<?php

namespace Tests\Unit;

use App\Models\EnglishEvaluation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnglishEvaluationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que el nivel CEFR se clasifica correctamente desde el score
     */
    public function test_cefr_level_classification_from_score(): void
    {
        $user = User::factory()->create();

        // Test A1 (0-30)
        $eval = EnglishEvaluation::create([
            'user_id' => $user->id,
            'score' => 25,
            'attempt_number' => 1,
        ]);
        $this->assertEquals('A1', $eval->cefr_level);

        // Test A2 (31-45)
        $eval2 = EnglishEvaluation::create([
            'user_id' => $user->id,
            'score' => 40,
            'attempt_number' => 2,
        ]);
        $this->assertEquals('A2', $eval2->cefr_level);

        // Test B1 (46-60)
        $eval3 = EnglishEvaluation::create([
            'user_id' => $user->id,
            'score' => 55,
            'attempt_number' => 3,
        ]);
        $this->assertEquals('B1', $eval3->cefr_level);
    }

    /**
     * Test que un usuario no puede tener más de 3 intentos
     */
    public function test_user_cannot_exceed_three_attempts(): void
    {
        $user = User::factory()->create();

        // Crear 3 evaluaciones
        for ($i = 1; $i <= 3; $i++) {
            EnglishEvaluation::create([
                'user_id' => $user->id,
                'score' => 50 + $i,
                'attempt_number' => $i,
            ]);
        }

        $this->assertEquals(3, $user->englishEvaluations()->count());
        $this->assertTrue($user->hasReachedMaxAttempts());
    }

    /**
     * Test que se obtiene el mejor intento correctamente
     */
    public function test_get_best_attempt(): void
    {
        $user = User::factory()->create();

        EnglishEvaluation::create([
            'user_id' => $user->id,
            'score' => 50,
            'attempt_number' => 1,
        ]);

        EnglishEvaluation::create([
            'user_id' => $user->id,
            'score' => 75,
            'attempt_number' => 2,
        ]);

        EnglishEvaluation::create([
            'user_id' => $user->id,
            'score' => 60,
            'attempt_number' => 3,
        ]);

        $best = $user->getBestEnglishEvaluation();
        $this->assertEquals(75, $best->score);
        $this->assertEquals('B2', $best->cefr_level);
    }

    /**
     * Test clasificación de todos los niveles CEFR
     */
    public function test_all_cefr_levels_classification(): void
    {
        $user = User::factory()->create();

        $testCases = [
            ['score' => 20, 'expected' => 'A1'],
            ['score' => 35, 'expected' => 'A2'],
            ['score' => 50, 'expected' => 'B1'],
            ['score' => 65, 'expected' => 'B1+'],
            ['score' => 75, 'expected' => 'B2'],
            ['score' => 85, 'expected' => 'C1'],
            ['score' => 95, 'expected' => 'C2'],
        ];

        foreach ($testCases as $index => $case) {
            $eval = EnglishEvaluation::create([
                'user_id' => $user->id,
                'score' => $case['score'],
                'attempt_number' => $index + 1,
            ]);
            
            $this->assertEquals(
                $case['expected'], 
                $eval->cefr_level,
                "Score {$case['score']} should be classified as {$case['expected']}"
            );
        }
    }
}
