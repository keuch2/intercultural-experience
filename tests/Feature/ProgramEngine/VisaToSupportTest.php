<?php

namespace Tests\Feature\ProgramEngine;

use App\Models\Program;
use App\Models\ProgramProcess;
use App\Services\ProgramEngine\ProgramDefinition;
use App\Services\ProgramEngine\StageEvaluator;
use Database\Seeders\WorkTravelProgramSeeder;
use Laravel\Sanctum\Sanctum;

/**
 * Con visa aprobada y documentos en regla el participante pasa a Support (no a Completado):
 * guard require_visa_approved, card "Siguiente etapa" en el tab visa, finalización solo en Support.
 */
class VisaToSupportTest extends EngineTestCase
{
    private Program $program;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WorkTravelProgramSeeder::class);
        $this->program = Program::where('slug', 'work-travel')->firstOrFail();
    }

    private function atVisa(): ProgramProcess
    {
        $process = $this->processFor($this->participant(), $this->program);
        $process->update(['current_stage_key' => 'visa']);

        return $process->fresh();
    }

    private function setVisaResult(ProgramProcess $process, string $result): void
    {
        $process->visaProcess()->firstOrCreate([])->update(['interview_result' => $result]);
    }

    private function approveVisaDocs(ProgramProcess $process): void
    {
        foreach (ProgramDefinition::for($this->program)->requirements('visa')->where('is_required', true) as $req) {
            $this->approvedDoc($process, $req->key, 'visa', max(1, (int) $req->min_count));
        }
    }

    public function test_visa_stage_is_blocked_until_visa_approved_and_docs_ok(): void
    {
        $process = $this->atVisa();
        $evaluator = app(StageEvaluator::class);

        $reasons = $evaluator->blockingReasons($process);
        $this->assertContains('Visa aún no aprobada.', $reasons);
        $this->assertFalse($evaluator->canAdvance($process));

        $this->setVisaResult($process, 'denied');
        $this->assertContains('Visa aún no aprobada.', $evaluator->blockingReasons($process->fresh()));

        $this->setVisaResult($process, 'approved');
        $this->approveVisaDocs($process);
        $this->assertSame([], $evaluator->blockingReasons($process->fresh()));
    }

    public function test_hub_shows_next_stage_card_in_visa_and_finalization_only_in_support(): void
    {
        $admin = $this->admin();
        $process = $this->atVisa();
        $url = fn (string $name, array $extra = []) => route("admin.program.{$name}", array_merge(['program' => $this->program->slug, 'process' => $process->id], $extra));

        $visaTab = $this->actingAs($admin)->get($url('participants.show', ['tab' => 'visa']))->assertOk();
        $visaTab->assertSee('Siguiente etapa: Support')
            ->assertSee('Visa aún no aprobada.')
            ->assertDontSee('Finalización del programa')
            ->assertDontSee('Requisitos de "Gestión de Visa J1" completos');

        // Sin visa aprobada solo se puede forzar; el avance normal falla
        $this->actingAs($admin)->post($url('stage.advance'))->assertSessionHas('error');
        $this->assertSame('visa', $process->fresh()->current_stage_key);

        $this->setVisaResult($process, 'approved');
        $this->approveVisaDocs($process);

        $this->actingAs($admin)->get($url('participants.show', ['tab' => 'visa']))->assertOk()
            ->assertSee('Requisitos completos')->assertSee('Avanzar a Support');

        $this->actingAs($admin)->post($url('stage.advance'))->assertSessionHas('success');
        $this->assertSame('support', $process->fresh()->current_stage_key);
        $this->assertSame('active', $process->fresh()->status);

        // En Support: finalización visible y etapa manual hacia Completado
        $this->actingAs($admin)->get($url('participants.show', ['tab' => 'support']))->assertOk()
            ->assertSee('Finalización del programa')->assertSee('Etapa manual');

        $this->actingAs($admin)->put($url('finalization.update'), ['finalization_result' => 'success'])->assertRedirect($url('participants.show', ['tab' => 'support']));
        $this->assertSame('completed', $process->fresh()->current_stage_key);
    }

    public function test_api_next_action_reflects_visa_approval_and_support(): void
    {
        $process = $this->atVisa();
        Sanctum::actingAs($process->user);

        $this->getJson('/api/programs/work-travel/process')->assertOk()->assertJsonPath('data.next_action.key', 'upload_docs');

        $this->approveVisaDocs($process);
        $this->getJson('/api/programs/work-travel/process')->assertJsonPath('data.next_action.key', 'stage_screen');

        $this->setVisaResult($process, 'approved');
        $this->getJson('/api/programs/work-travel/process')->assertJsonPath('data.next_action.key', 'wait_visa_confirmation');

        $process->update(['current_stage_key' => 'support']);
        $this->getJson('/api/programs/work-travel/process')->assertOk()
            ->assertJsonPath('data.current_stage', 'support')
            ->assertJsonPath('data.next_action.key', 'support')
            ->assertJsonPath('data.next_action.screen', 'ProgramSupport');
    }

    public function test_seeder_and_config_expose_visa_guard(): void
    {
        $visa = ProgramDefinition::for($this->program)->stage('visa');
        $this->assertTrue((bool) $visa->guardValue('require_visa_approved'));
        $this->assertFalse((bool) $visa->guardValue('manual_only'));
        $this->assertTrue($visa->hasAutomaticGuards());
        $this->assertFalse(ProgramDefinition::for($this->program)->stage('support')->hasAutomaticGuards());

        $admin = $this->admin();
        $this->actingAs($admin)->put(route('admin.program-config.stages.update', [$this->program, $visa]), [
            'key' => 'visa', 'label' => 'Gestión de Visa J1', 'sort_order' => 5,
            'guards' => ['require_docs_approved' => 1, 'require_visa_approved' => 1],
        ])->assertRedirect();
        $this->assertTrue((bool) $visa->fresh()->guardValue('require_visa_approved'));
        $this->actingAs($admin)->get(route('admin.program-config.show', [$this->program, 'tab' => 'stages']))->assertOk()->assertSee('>visa<', false);
    }
}
