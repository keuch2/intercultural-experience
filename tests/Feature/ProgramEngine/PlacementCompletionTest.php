<?php

namespace Tests\Feature\ProgramEngine;

use App\Models\Program;
use App\Models\Sponsor;
use App\Services\ProgramEngine\JobPoolService;
use App\Services\ProgramEngine\PlacementService;
use App\Services\ProgramEngine\StageEvaluator;
use Database\Seeders\WorkTravelProgramSeeder;

class PlacementCompletionTest extends EngineTestCase
{
    public function test_placement_completes_with_sponsor_docs_sevis_and_ds2019_and_unlocks_stage(): void
    {
        $this->seed(WorkTravelProgramSeeder::class);
        $program = Program::where('slug', 'work-travel')->firstOrFail();
        $placements = app(PlacementService::class);
        $evaluator = new StageEvaluator;

        $process = $this->processFor($this->participant(), $program);
        $process->update(['module_access' => ['job_pool' => ['enabled' => true]], 'current_stage_key' => 'job_pool']);
        $process = $process->fresh();

        // job_pool requiere oferta asignada
        $this->assertStringContainsString('oferta laboral', implode(' ', $evaluator->blockingReasons($process)));
        $offer = app(JobPoolService::class)->publish($program, ['employer_name' => 'Cedar Point', 'state' => 'Ohio', 'city' => 'Sandusky', 'positions_total' => 1], null, $this->admin());
        app(JobPoolService::class)->select($offer, $process);
        $this->assertTrue($evaluator->canAdvance($process->fresh()));

        // placement requiere docs del sponsor aprobados + SEVIS + DS-2019
        $process->update(['current_stage_key' => 'placement']);
        $process = $process->fresh();
        $placement = $placements->ensure($process);
        $this->assertSame($offer->id, $placement->assignment->job_pool_offer_id);
        $this->assertFalse($placements->isComplete($process));

        $placements->update($process, ['sponsor_id' => Sponsor::where('code', 'AAG')->first()->id, 'sevis_number' => 'N001', 'ds2019_number' => 'DS-1', 'terms_accepted' => true]);
        $this->assertFalse($placements->isComplete($process->fresh()), 'faltan docs del sponsor');
        $this->assertSame('in_progress', $process->fresh()->placement->status);

        foreach (['sponsor_terms', 'student_proof', 'student_interview'] as $key) {
            $this->approvedDoc($process, $key, 'placement');
        }
        $process = $process->fresh();
        $this->assertTrue($placements->isComplete($process));
        $this->assertTrue($evaluator->canAdvance($process));

        $placements->update($process, ['ds_tracking_number' => 'TRK1']);
        $this->assertSame('ds_shipped', $process->fresh()->placement->status);
        $placements->update($process, ['ds_received_at' => now()->toDateString()]);
        $this->assertSame('completed', $process->fresh()->placement->status);

        $payload = $placements->toArray($process->fresh()->load('placement.sponsor', 'program'));
        $this->assertTrue($payload['has_assignment']);
        $this->assertSame('Cedar Point', $payload['offer']['employer_name']);
        $this->assertSame('AAG', $payload['placement']['sponsor']);
        $this->assertTrue($payload['placement']['is_complete']);
    }
}
