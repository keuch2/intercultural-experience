<?php

namespace Tests\Feature\ProgramEngine;

use App\Events\ProgramEngine\ProgramProcessStageChanged;
use App\Services\ProgramEngine\EnvelopeBuilder;
use App\Services\ProgramEngine\Exceptions\StageTransitionException;
use App\Services\ProgramEngine\StageAdvancer;
use Illuminate\Support\Facades\Event;

class StageAdvancerTest extends EngineTestCase
{
    public function test_advance_throws_with_reasons_when_guards_fail(): void
    {
        $program = $this->engineProgram();
        $process = $this->processFor($this->participant(), $program);

        try {
            (new StageAdvancer)->advance($process, $this->admin());
            $this->fail('Debió lanzar StageTransitionException');
        } catch (StageTransitionException $e) {
            $this->assertNotEmpty($e->reasons);
        }
        $this->assertSame('admission', $process->fresh()->current_stage_key);
    }

    public function test_force_advance_ignores_guards_but_not_terminal(): void
    {
        Event::fake([ProgramProcessStageChanged::class]);
        $program = $this->engineProgram();
        $process = $this->processFor($this->participant(), $program);
        $admin = $this->admin();
        $advancer = new StageAdvancer;

        $advancer->advance($process, $admin, force: true);
        $process->refresh();
        $this->assertSame('application', $process->current_stage_key);
        $this->assertSame('approved', $process->stageStatus('admission'));
        $this->assertSame('in_progress', $process->stageStatus('application'));
        $this->assertSame('application', $process->application->fresh()->current_stage);
        Event::assertDispatched(ProgramProcessStageChanged::class, fn ($e) => $e->fromStage === 'admission' && $e->toStage === 'application');

        $advancer->advance($process, $admin, force: true);
        $process->refresh();
        $this->assertSame('completed', $process->current_stage_key);
        $this->assertSame('completed', $process->status);
        $this->assertNotNull($process->application->fresh()->completed_at);

        $this->expectException(StageTransitionException::class);
        $advancer->advance($process, $admin, force: true);
    }

    public function test_advance_succeeds_when_guards_pass_and_logs_activity(): void
    {
        $program = $this->engineProgram();
        $process = $this->processFor($this->participant(), $program);
        $this->approvedDoc($process, 'cedula', 'admission');

        (new StageAdvancer)->advance($process, $this->admin());

        $this->assertSame('application', $process->fresh()->current_stage_key);
        $this->assertDatabaseHas('activity_logs', ['log_name' => 'programa-test', 'action' => 'stage_advanced']);
    }

    public function test_revert_relocks_later_stages(): void
    {
        $program = $this->engineProgram();
        $process = $this->processFor($this->participant(), $program);
        $advancer = new StageAdvancer;
        $advancer->advance($process, null, force: true);

        $advancer->revert($process->fresh(), 'admission', $this->admin(), 'Error de carga');
        $process->refresh();

        $this->assertSame('admission', $process->current_stage_key);
        $this->assertSame('in_progress', $process->stageStatus('admission'));
        $this->assertSame('locked', $process->stageStatus('application'));
    }

    public function test_envelope_exposes_stages_gates_checklist_groups_and_next_action(): void
    {
        $program = $this->engineProgram();
        $process = $this->processFor($this->participant(), $program);

        $env = (new EnvelopeBuilder)->build($process);

        $this->assertSame('programa-test', $env['program']['slug']);
        $this->assertSame(['admission', 'application', 'completed'], array_column($env['stages'], 'key'));
        $this->assertSame('in_progress', $env['stages'][0]['state']);
        $this->assertSame('locked', $env['stages'][1]['state']);
        $this->assertFalse($env['flags']['inscription']);
        $this->assertFalse($env['flags']['contract_signed']);
        $this->assertSame('upload_docs', $env['next_action']['key']);
        $this->assertSame('ProgramDocuments', $env['next_action']['screen']);

        $groups = collect($env['document_groups'])->keyBy('key');
        $this->assertTrue($groups['admission']['unlocked']);
        $this->assertFalse($groups['application']['unlocked']);
        $this->assertSame('stage_locked', $groups['application']['lock_reason']);
        $this->assertSame(['required' => 1, 'approved' => 0, 'pending' => 0, 'missing' => 1], $groups['admission']['counts']);
        $this->assertArrayHasKey('english_test', $env['modules']);
    }

    public function test_pending_approval_blocks_next_action_and_groups(): void
    {
        $program = $this->engineProgram();
        $process = $this->processFor($this->participant(), $program, 'pending');

        $env = (new EnvelopeBuilder)->build($process);

        $this->assertFalse($env['application_approved']);
        $this->assertSame('wait_approval', $env['next_action']['key']);
        $this->assertSame('pending_approval', $env['document_groups'][0]['lock_reason']);
    }
}
