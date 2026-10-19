<?php

namespace Tests\Feature\ProgramEngine;

use App\Models\JobPoolAssignment;
use App\Models\JobPoolOffer;
use App\Models\Program;
use App\Models\ProgramProcess;
use App\Services\ProgramEngine\Exceptions\JobPoolException;
use App\Services\ProgramEngine\JobPoolService;
use Database\Seeders\WorkTravelProgramSeeder;
use Illuminate\Support\Facades\DB;

class JobPoolSelectionTest extends EngineTestCase
{
    private Program $program;

    private JobPoolService $pool;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WorkTravelProgramSeeder::class);
        $this->program = Program::where('slug', 'work-travel')->firstOrFail();
        $this->pool = app(JobPoolService::class);
    }

    private function enabledProcess(): ProgramProcess
    {
        $process = $this->processFor($this->participant(), $this->program);
        $process->update(['module_access' => ['job_pool' => ['enabled' => true]]]);

        return $process->fresh();
    }

    private function offer(int $positions = 2): JobPoolOffer
    {
        return $this->pool->publish($this->program, ['employer_name' => 'Disney', 'state' => 'Florida', 'city' => 'Orlando', 'positions_total' => $positions], null, $this->admin());
    }

    public function test_select_decrements_positions_and_records_history(): void
    {
        $offer = $this->offer(2);
        $process = $this->enabledProcess();

        $assignment = $this->pool->select($offer, $process);

        $this->assertSame(1, $offer->fresh()->positions_available);
        $this->assertSame($process->id, $assignment->active_process_id);
        $this->assertDatabaseHas('job_pool_events', ['job_pool_offer_id' => $offer->id, 'program_process_id' => $process->id, 'event_type' => 'selected', 'actor_type' => 'participant']);
        $this->assertTrue($process->fresh()->activeJobAssignment()->exists());
    }

    public function test_participant_cannot_select_twice_or_without_access(): void
    {
        $offer = $this->offer(3);
        $process = $this->enabledProcess();
        $this->pool->select($offer, $process);

        // Al elegir se cierra el acceso al pool.
        $this->assertFalse($process->fresh()->hasModuleAccess('job_pool'));
        try {
            $this->pool->select($this->offer(3), $process);
            $this->fail('Debió rechazar la segunda selección');
        } catch (JobPoolException $e) {
            $this->assertSame('not_enabled', $e->errorCode);
        }

        // IE rehabilita el acceso sin autorizar el cambio: sigue sin poder elegir otra.
        $this->pool->setPoolAccess($process->fresh(), true, $this->admin(), allowReselect: false);
        try {
            $this->pool->select($this->offer(3), $process->fresh());
            $this->fail('Debió rechazar sin autorización de cambio');
        } catch (JobPoolException $e) {
            $this->assertSame('already_assigned', $e->errorCode);
        }

        $noAccess = $this->processFor($this->participant(), $this->program);
        try {
            $this->pool->select($offer, $noAccess);
            $this->fail('Debió rechazar sin acceso');
        } catch (JobPoolException $e) {
            $this->assertSame('not_enabled', $e->errorCode);
        }
    }

    public function test_exhausted_offer_disappears_and_rejects_further_selection(): void
    {
        $offer = $this->offer(1);
        $first = $this->enabledProcess();
        $second = $this->enabledProcess();

        $this->pool->select($offer, $first);
        $this->assertSame(0, $offer->fresh()->positions_available);
        $this->assertCount(0, $this->pool->availableFor($second));
        $this->assertDatabaseHas('job_pool_events', ['job_pool_offer_id' => $offer->id, 'event_type' => 'positions_exhausted']);

        try {
            $this->pool->select($offer, $second);
            $this->fail('Debió rechazar por falta de cupo');
        } catch (JobPoolException $e) {
            $this->assertSame('no_positions', $e->errorCode);
        }
        $this->assertSame(0, $offer->fresh()->positions_available, 'el cupo no debe quedar negativo ni cambiar');
    }

    public function test_release_restores_position_and_allows_reselection(): void
    {
        $offer = $this->offer(1);
        $process = $this->enabledProcess();
        $assignment = $this->pool->select($offer, $process);

        $this->pool->release($assignment, $this->admin(), 'Cambio de planes');

        $this->assertSame(1, $offer->fresh()->positions_available);
        $this->assertSame('released', $assignment->fresh()->status);
        $this->assertNull($assignment->fresh()->active_process_id);
        $this->assertFalse($process->fresh()->activeJobAssignment()->exists());

        // Una liberación común no rehabilita el pool: lo decide IE.
        $this->assertFalse($process->fresh()->hasModuleAccess('job_pool'));
        $this->pool->setPoolAccess($process->fresh(), true, $this->admin());
        $again = $this->pool->select($offer, $process->fresh());
        $this->assertSame('active', $again->status);
        $this->assertSame(0, $offer->fresh()->positions_available);
    }

    public function test_authorized_change_releases_previous_offer_automatically(): void
    {
        $first = $this->offer(1);
        $second = $this->offer(1);
        $process = $this->enabledProcess();
        $old = $this->pool->select($first, $process);
        $this->assertSame(0, $first->fresh()->positions_available);

        // IE habilita el acceso con "Autorizar cambio de oferta"
        $this->pool->setPoolAccess($process->fresh(), true, $this->admin(), allowReselect: true);
        $new = $this->pool->select($second, $process->fresh());

        $this->assertSame(JobPoolAssignment::STATUS_CHANGED, $old->fresh()->status);
        $this->assertSame(1, $first->fresh()->positions_available, 'la oferta anterior queda libre');
        $this->assertSame(0, $second->fresh()->positions_available);
        $this->assertSame($process->id, $new->active_process_id);
        $access = $process->fresh()->moduleAccess('job_pool');
        $this->assertFalse($access['enabled']);
        $this->assertFalse($access['allow_reselect']);
        $this->assertDatabaseHas('job_placements', ['program_process_id' => $process->id, 'job_pool_assignment_id' => $new->id]);
        $this->assertDatabaseHas('job_pool_events', ['program_process_id' => $process->id, 'event_type' => 'changed']);
    }

    public function test_job_cancelled_reenables_pool_and_applies_offer_action(): void
    {
        $offer = $this->offer(2);
        $process = $this->enabledProcess();
        $assignment = $this->pool->select($offer, $process);

        $this->pool->cancelJob($assignment, $this->admin(), 'Cerró el hotel', 'pause');

        $this->assertSame(JobPoolAssignment::STATUS_JOB_CANCELLED, $assignment->fresh()->status);
        $this->assertSame('paused', $offer->fresh()->status);
        $this->assertSame(2, $offer->fresh()->positions_available);
        $this->assertTrue($process->fresh()->hasModuleAccess('job_pool'), 'vuelve a poder elegir');
        $this->assertFalse($process->fresh()->activeJobAssignment()->exists());

        $other = $this->offer(1);
        $assignment2 = $this->pool->select($other, $process->fresh());
        $this->pool->cancelJob($assignment2, $this->admin(), null, 'delete');
        $this->assertSoftDeleted('job_pool_offers', ['id' => $other->id]);
    }

    public function test_failed_interview_releases_offer_and_reenables_pool(): void
    {
        $offer = $this->offer(1);
        $process = $this->enabledProcess();
        $this->pool->select($offer, $process);
        $placements = app(\App\Services\ProgramEngine\PlacementService::class);

        $outcome = $placements->updateWithOutcome($process->fresh(), ['sponsor_interview_result' => 'approved', 'employer_interview_result' => 'pending']);
        $this->assertFalse($outcome['released']);
        $this->assertNull($outcome['placement']->job_accepted_at);

        $outcome = $placements->updateWithOutcome($process->fresh(), ['employer_interview_result' => 'not_approved']);
        $this->assertTrue($outcome['released']);
        $this->assertFalse($process->fresh()->activeJobAssignment()->exists());
        $this->assertSame(1, $offer->fresh()->positions_available);
        $this->assertTrue($process->fresh()->hasModuleAccess('job_pool'));
    }

    public function test_passed_interviews_record_job_acceptance_date(): void
    {
        $process = $this->enabledProcess();
        $this->pool->select($this->offer(1), $process);
        $placements = app(\App\Services\ProgramEngine\PlacementService::class);

        $placement = $placements->update($process->fresh(), ['sponsor_interview_result' => 'not_applicable', 'employer_interview_result' => 'approved']);

        $this->assertSame(today()->toDateString(), $placement->job_accepted_at->toDateString());
        $this->assertSame('job_accepted', $placement->status);
    }

    public function test_reassign_moves_offer_to_other_participant(): void
    {
        $offer = $this->offer(1);
        $from = $this->enabledProcess();
        $to = $this->enabledProcess();
        $assignment = $this->pool->select($offer, $from);

        $new = $this->pool->reassign($assignment, $to, $this->admin(), 'Error de carga');

        $this->assertSame('reassigned', $assignment->fresh()->status);
        $this->assertSame($to->id, $new->active_process_id);
        $this->assertSame(0, $offer->fresh()->positions_available);
        $this->assertDatabaseHas('job_pool_events', ['program_process_id' => $from->id, 'event_type' => 'reassigned']);
    }

    public function test_conditional_decrement_returns_zero_rows_when_exhausted_and_unique_blocks_double_active(): void
    {
        $offer = $this->offer(1);
        $process = $this->enabledProcess();
        $this->pool->select($offer, $process);

        // Simula la carrera por el último cupo: el UPDATE condicional no afecta filas.
        $affected = JobPoolOffer::whereKey($offer->id)->where('positions_available', '>', 0)->decrement('positions_available');
        $this->assertSame(0, $affected);
        $this->assertSame(0, $offer->fresh()->positions_available);

        // Doble asignación activa del mismo participante: la rechaza el índice UNIQUE.
        $this->expectException(\Illuminate\Database\QueryException::class);
        JobPoolAssignment::create(['job_pool_offer_id' => $this->offer(5)->id, 'program_process_id' => $process->id, 'active_process_id' => $process->id, 'status' => 'active', 'selected_at' => now()]);
    }

    public function test_cannot_reduce_positions_below_taken_or_delete_with_active_assignments(): void
    {
        $offer = $this->offer(2);
        $process = $this->enabledProcess();
        $this->pool->select($offer, $process);

        try {
            $this->pool->update($offer, ['positions_total' => 0], null, $this->admin());
            $this->fail('Debió rechazar');
        } catch (JobPoolException $e) {
            $this->assertSame('positions_below_taken', $e->errorCode);
        }

        $this->pool->update($offer, ['positions_total' => 5], null, $this->admin());
        $this->assertSame(4, $offer->fresh()->positions_available);

        try {
            $this->pool->delete($offer, $this->admin());
            $this->fail('Debió rechazar');
        } catch (JobPoolException $e) {
            $this->assertSame('has_active_assignments', $e->errorCode);
        }
    }

    public function test_paused_offer_is_not_selectable(): void
    {
        $offer = $this->offer(2);
        $process = $this->enabledProcess();
        $this->pool->pause($offer, $this->admin());
        $this->assertCount(0, $this->pool->availableFor($process));

        try {
            $this->pool->select($offer, $process);
            $this->fail('Debió rechazar');
        } catch (JobPoolException $e) {
            $this->assertSame('offer_unavailable', $e->errorCode);
        }
        $this->pool->reactivate($offer, $this->admin());
        $this->assertCount(1, $this->pool->availableFor($process));
        $this->assertGreaterThan(0, DB::table('job_pool_events')->where('event_type', 'offer_reactivated')->count());
    }
}
