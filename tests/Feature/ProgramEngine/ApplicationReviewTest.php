<?php

namespace Tests\Feature\ProgramEngine;

use App\Models\Application;
use App\Models\AuPairDocument;
use App\Models\AuPairProcess;
use App\Models\Notification;
use App\Models\Program;
use App\Models\ProgramProcess;

/**
 * Pedido de IE (nov 2026): el perfil/proceso del programa se crea recién cuando IE aprueba la solicitud;
 * al aprobar o rechazar se avisa al participante; una solicitud rechazada queda como historial (no se ve
 * en la app) y la persona puede volver a postular.
 */
class ApplicationReviewTest extends EngineTestCase
{
    private function auPairProgram(): Program
    {
        return Program::create(['name' => 'Au Pair USA', 'description' => 'x', 'country' => 'USA', 'main_category' => 'IE',
            'subcategory' => 'Au Pair', 'is_active' => true, 'is_available_in_app' => true]);
    }

    public function test_applying_from_the_app_does_not_create_the_engine_process(): void
    {
        $program = $this->engineProgram();
        $user = $this->participant();
        $admin = $this->admin();

        $this->actingAs($user, 'sanctum')->postJson('/api/applications', ['program_id' => $program->id])->assertCreated();

        $this->assertSame(0, ProgramProcess::count());
        $this->assertTrue(Notification::where('user_id', $admin->id)->where('title', 'Nueva solicitud de acceso')->exists());

        // La app (incluida la versión en tiendas) sigue viendo "Aprobación pendiente" sin que se cree nada.
        $this->actingAs($user, 'sanctum')->getJson("/api/programs/{$program->slug}/process")
            ->assertOk()->assertJsonPath('data.application_approved', false)->assertJsonPath('data.current_stage', 'admission')->assertJsonPath('data.id', null)->assertJsonPath('data.application_id', Application::where('user_id', $user->id)->value('id'));
        $this->actingAs($user, 'sanctum')->getJson('/api/me/process')->assertOk()->assertJsonPath('data.application_approved', false);
        $this->actingAs($user, 'sanctum')->getJson("/api/programs/{$program->slug}/documents")
            ->assertStatus(403)->assertJsonPath('status', 'application_pending');
        $this->assertSame(0, ProgramProcess::count());
    }

    public function test_approving_from_the_inbox_creates_the_process_and_notifies(): void
    {
        $program = $this->engineProgram();
        $user = $this->participant();
        $application = $this->applicationFor($user, $program, 'pending');

        $this->actingAs($this->admin())->get(route('admin.applications.index'))
            ->assertOk()->assertSee('Por revisar')->assertSee($user->name);

        $this->actingAs($this->admin())->post(route('admin.applications.approve', $application))->assertSessionHas('success');

        $application->refresh();
        $this->assertSame('approved', $application->status);
        $this->assertNotNull($application->reviewed_at);
        $this->assertSame(1, ProgramProcess::where('application_id', $application->id)->count());
        $this->assertTrue(Notification::where('user_id', $user->id)->where('title', 'Tu postulación fue aprobada')->exists());

        $this->actingAs($user, 'sanctum')->getJson("/api/programs/{$program->slug}/process")->assertJsonPath('data.application_approved', true);
    }

    public function test_rejecting_keeps_history_hides_it_from_the_app_and_allows_reapplying(): void
    {
        $program = $this->auPairProgram();
        $user = $this->participant();
        $application = $this->applicationFor($user, $program, 'pending');
        // Proceso creado antes de este cambio (se creaba al postular), con un documento.
        $process = AuPairProcess::create(AuPairProcess::initialAttributes($application));
        AuPairDocument::create(['au_pair_process_id' => $process->id, 'user_id' => $user->id, 'document_type' => 'cedula', 'stage' => 'admission',
            'file_path' => 'test/cedula.pdf', 'original_filename' => 'cedula.pdf', 'status' => 'pending']);

        $this->actingAs($this->admin())->post(route('admin.applications.reject', $application), ['rejection_reason' => 'Perfil incompleto'])
            ->assertSessionHas('success');

        $application->refresh();
        $this->assertSame('rejected', $application->status);
        $this->assertSame('Perfil incompleto', $application->rejection_reason);
        $this->assertSame(0, AuPairProcess::withTrashed()->where('application_id', $application->id)->count());
        $this->assertSame(0, AuPairDocument::where('au_pair_process_id', $process->id)->count());
        $this->assertTrue(Notification::where('user_id', $user->id)->where('title', 'Tu postulación no fue aprobada')
            ->where('message', 'like', '%Perfil incompleto%')->exists());

        // La app no muestra la rechazada ni "Aprobación pendiente".
        $this->actingAs($user, 'sanctum')->getJson('/api/applications')->assertOk()->assertJsonCount(0, 'data');
        $this->actingAs($user, 'sanctum')->getJson('/api/au-pair/process')->assertNotFound()->assertJsonPath('status', 'no_application');

        // Historial en el admin y puede volver a postular.
        $this->actingAs($this->admin())->get(route('admin.applications.index', ['status' => 'rejected']))
            ->assertOk()->assertSee('Motivo: Perfil incompleto');
        $this->actingAs($user, 'sanctum')->postJson('/api/applications', ['program_id' => $program->id])->assertCreated();
        $this->assertSame(2, Application::where('user_id', $user->id)->count());
    }

    public function test_au_pair_pending_application_gets_a_preview_without_creating_the_process(): void
    {
        $program = $this->auPairProgram();
        $user = $this->participant();
        $application = $this->applicationFor($user, $program, 'pending');

        $this->actingAs($user, 'sanctum')->getJson('/api/au-pair/process')
            ->assertOk()->assertJsonPath('data.application_approved', false)->assertJsonPath('data.current_stage', 'admission');
        $this->assertSame(0, AuPairProcess::count());

        // El perfil Au Pair del admin no se crea solo: manda a la bandeja.
        $this->actingAs($this->admin())->get(route('admin.aupair.profiles.show', $user->id))
            ->assertRedirect(route('admin.applications.index', ['search' => $user->email]));
        $this->assertSame(0, AuPairProcess::count());

        app(\App\Services\ApplicationReviewService::class)->approve($application, null);
        $this->assertSame(1, AuPairProcess::where('application_id', $application->id)->count());
    }

    public function test_staff_created_application_is_approved_with_its_process(): void
    {
        $program = $this->engineProgram();
        $user = $this->participant();

        $this->actingAs($this->admin())->post(route('admin.participants.applications.store', $user), ['program_id' => $program->id])
            ->assertSessionHasNoErrors();

        $application = Application::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('approved', $application->status);
        $this->assertSame(1, ProgramProcess::where('application_id', $application->id)->count());
    }

    public function test_an_approved_application_cannot_be_rejected_from_the_inbox(): void
    {
        $program = $this->engineProgram();
        $process = $this->processFor($this->participant(), $program);

        $this->actingAs($this->admin())->post(route('admin.applications.reject', $process->application))->assertSessionHas('error');
        $this->assertSame('approved', $process->application->fresh()->status);
        $this->assertNotNull($process->fresh());
    }
}
