<?php

namespace Tests\Feature\ProgramEngine;

use App\Models\Application;
use App\Models\AuPairProcess;
use App\Models\Program;
use App\Services\ProgramEngine\StageAdvancer;
use Database\Seeders\WorkTravelProgramSeeder;
use Laravel\Sanctum\Sanctum;

/** Tras la finalización de IE el participante puede postular a una nueva convocatoria sin perder la anterior. */
class ReapplyAfterFinalizationTest extends EngineTestCase
{
    public function test_engine_participant_reapplies_after_finalization_and_keeps_history(): void
    {
        $this->seed(WorkTravelProgramSeeder::class);
        $program = Program::where('slug', 'work-travel')->firstOrFail();
        $program->update(['is_available_in_app' => true]);
        $old = $this->processFor($this->participant(), $program);
        $user = $old->user;
        Sanctum::actingAs($user);

        // Con el programa en curso no puede volver a postular
        $this->postJson('/api/applications', ['program_id' => $program->id])->assertStatus(409)->assertJsonPath('code', 'already_applied');
        $this->getJson('/api/applications')->assertJsonPath('data.0.is_finished', false);

        app(StageAdvancer::class)->finalize($old, $this->admin(), 'success');
        $this->getJson('/api/applications')->assertJsonPath('data.0.is_finished', true);

        $this->postJson('/api/applications', ['program_id' => $program->id])->assertCreated();
        $newApp = Application::where('user_id', $user->id)->latest('id')->firstOrFail();
        $this->assertNotSame($old->application_id, $newApp->id);

        // Sin selección: la nueva convocatoria; con X-Application-Id: la participación anterior
        $this->getJson('/api/programs/work-travel/process')->assertOk()->assertJsonPath('data.application_id', $newApp->id)->assertJsonPath('data.status', 'active');
        $this->getJson('/api/programs/work-travel/process', ['X-Application-Id' => $old->application_id])->assertOk()
            ->assertJsonPath('data.application_id', $old->application_id)->assertJsonPath('data.status', 'completed');

        // Una postulación ajena no se puede seleccionar
        $foreign = $this->processFor($this->participant(), $program);
        $this->getJson('/api/programs/work-travel/process', ['X-Application-Id' => $foreign->application_id])->assertOk()->assertJsonPath('data.application_id', $newApp->id);
    }

    public function test_au_pair_participant_reapplies_after_finalization(): void
    {
        $program = Program::create(['name' => 'Au Pair USA', 'description' => 'x', 'country' => 'USA', 'main_category' => 'IE', 'subcategory' => 'Au Pair', 'is_active' => true, 'is_available_in_app' => true]);
        $user = $this->participant();
        $app = Application::create(['user_id' => $user->id, 'program_id' => $program->id, 'status' => 'approved', 'applied_at' => now()->subYear(), 'completed_at' => now()->subYear()]);
        $oldProcess = AuPairProcess::create(['application_id' => $app->id, 'user_id' => $user->id, 'current_stage' => 'support', 'admission_status' => 'approved', 'application_status' => 'approved', 'match_visa_status' => 'approved', 'support_status' => 'active']);
        Sanctum::actingAs($user);

        $this->postJson('/api/applications', ['program_id' => $program->id])->assertStatus(409);

        // IE registra la finalización (Au Pair legacy)
        $this->actingAs($this->admin())->put(route('admin.aupair.profiles.update-finalization', $user->id), ['finalization_result' => 'success'])->assertRedirect();
        $this->assertSame('completed', $oldProcess->fresh()->current_stage);

        Sanctum::actingAs($user);
        $this->postJson('/api/applications', ['program_id' => $program->id])->assertCreated();
        $newApp = Application::where('user_id', $user->id)->latest('id')->firstOrFail();

        $this->getJson('/api/au-pair/process')->assertOk()->assertJsonPath('data.application_id', $newApp->id)->assertJsonPath('data.current_stage', 'admission');
        $this->getJson('/api/au-pair/process', ['X-Application-Id' => $app->id])->assertOk()->assertJsonPath('data.current_stage', 'completed');
        $this->assertSame($newApp->id, $user->fresh()->auPairProcess->application_id, 'el perfil Au Pair muestra la convocatoria vigente');
    }
}
