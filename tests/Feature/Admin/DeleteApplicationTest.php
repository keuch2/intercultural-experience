<?php

namespace Tests\Feature\Admin;

use App\Models\Application;
use App\Models\AuPairDocument;
use App\Models\AuPairProcess;
use App\Models\Currency;
use App\Models\Payment;
use App\Models\Program;
use App\Models\ProgramDocument;
use App\Models\ProgramProcess;
use App\Services\ProgramEngine\JobPoolService;
use Database\Seeders\WorkTravelProgramSeeder;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\ProgramEngine\EngineTestCase;

/**
 * "Eliminar postulación": borrado definitivo (proceso, documentos, pagos) que deja al
 * participante libre para volver a postular; el usuario nunca se borra.
 */
class DeleteApplicationTest extends EngineTestCase
{
    private Program $program;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WorkTravelProgramSeeder::class);
        $this->program = Program::where('slug', 'work-travel')->firstOrFail();
        Storage::fake('public');
    }

    private function payment(Application $app, string $status): Payment
    {
        return Payment::create([
            'application_id' => $app->id, 'user_id' => $app->user_id, 'program_id' => $app->program_id,
            'currency_id' => Currency::firstOrCreate(['code' => 'USD'], ['name' => 'Dólar', 'symbol' => '$', 'exchange_rate_to_pyg' => 1, 'is_active' => true])->id,
            'amount' => 100, 'converted_amount' => 100, 'concept' => 'Inscripción', 'payment_date' => now()->toDateString(), 'status' => $status,
        ]);
    }

    public function test_deleting_engine_application_cascades_restores_offer_and_allows_reapply(): void
    {
        $admin = $this->admin();
        $user = $this->participant();
        $process = $this->processFor($user, $this->program);
        $application = $process->application;

        Storage::disk('public')->put("program-docs/work-travel/{$process->id}/cedula/ci.pdf", 'x');
        ProgramDocument::create(['program_process_id' => $process->id, 'requirement_key' => 'cedula', 'stage_key' => 'admission', 'file_path' => "program-docs/work-travel/{$process->id}/cedula/ci.pdf", 'original_filename' => 'ci.pdf', 'status' => 'pending']);
        $offer = app(JobPoolService::class)->publish($this->program, ['job_title' => 'Lifeguard', 'employer_name' => 'Park', 'state' => 'FL', 'city' => 'Orlando', 'positions_total' => 1], null, $admin);
        app(JobPoolService::class)->select($offer, $process, $admin);
        $this->assertSame(0, $offer->fresh()->positions_available);
        $this->payment($application, 'pending');

        $page = $this->actingAs($admin)->get(route('admin.participants.show', $user->id))->assertOk();
        $page->assertSee(route('admin.participants.applications.destroy', [$user->id, $application->id]), false)->assertDontSee('confirmDelete(');

        $this->actingAs($admin)->delete(route('admin.participants.applications.destroy', [$user->id, $application->id]))
            ->assertRedirect(route('admin.participants.show', $user->id))->assertSessionHas('success');

        $this->assertDatabaseMissing('applications', ['id' => $application->id]);
        $this->assertSame(0, ProgramProcess::withTrashed()->where('id', $process->id)->count());
        $this->assertDatabaseCount('program_documents', 0);
        $this->assertDatabaseCount('job_pool_assignments', 0);
        $this->assertDatabaseCount('payments', 0);
        $this->assertSame(1, $offer->fresh()->positions_available);
        Storage::disk('public')->assertMissing("program-docs/work-travel/{$process->id}/cedula/ci.pdf");
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'application_deleted']);

        // Vuelve a postular desde la app
        Sanctum::actingAs($user);
        $this->postJson('/api/applications', ['program_id' => $this->program->id])->assertStatus(201);
    }

    public function test_verified_payments_require_explicit_confirmation(): void
    {
        $admin = $this->admin();
        $user = $this->participant();
        $application = $this->applicationFor($user, $this->program);
        $this->payment($application, 'verified');
        $url = route('admin.participants.applications.destroy', [$user->id, $application->id]);

        $this->actingAs($admin)->from(route('admin.participants.show', $user->id))->delete($url)
            ->assertRedirect(route('admin.participants.show', $user->id))->assertSessionHasErrors('application');
        $this->assertDatabaseHas('applications', ['id' => $application->id]);

        $this->actingAs($admin)->delete($url, ['confirm_payments' => 1])->assertSessionHas('success');
        $this->assertDatabaseMissing('applications', ['id' => $application->id]);
    }

    public function test_au_pair_application_delete_removes_process_and_files(): void
    {
        $admin = $this->admin();
        $auPair = Program::create(['name' => 'Au Pair USA', 'description' => 'x', 'country' => 'USA', 'main_category' => 'IE', 'subcategory' => 'Au Pair', 'is_active' => true]);
        $user = $this->participant();
        $application = Application::create(['user_id' => $user->id, 'program_id' => $auPair->id, 'status' => 'approved', 'applied_at' => now()]);
        $process = AuPairProcess::create(['application_id' => $application->id, 'user_id' => $user->id, 'current_stage' => 'admission', 'admission_status' => 'in_progress', 'application_status' => 'locked', 'match_visa_status' => 'locked', 'support_status' => 'locked']);
        Storage::disk('public')->put("au-pair-documents/{$user->id}/passport.jpg", 'x');
        AuPairDocument::create(['au_pair_process_id' => $process->id, 'document_type' => 'passport', 'stage' => 'admission', 'file_path' => "au-pair-documents/{$user->id}/passport.jpg", 'original_filename' => 'passport.jpg', 'file_size' => 1, 'status' => 'pending', 'min_count' => 1]);

        $this->actingAs($admin)->get(route('admin.aupair.profiles.show', ['id' => $user->id]))->assertOk()->assertSee('Eliminar postulación');
        $this->actingAs($admin)->delete(route('admin.participants.applications.destroy', [$user->id, $application->id]))->assertSessionHas('success');

        $this->assertDatabaseMissing('applications', ['id' => $application->id]);
        $this->assertDatabaseMissing('au_pair_processes', ['id' => $process->id]);
        $this->assertDatabaseCount('au_pair_documents', 0);
        Storage::disk('public')->assertMissing("au-pair-documents/{$user->id}/passport.jpg");
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_reapply_rule_is_shared_between_app_and_admin(): void
    {
        $admin = $this->admin();
        $user = $this->participant();
        $application = $this->applicationFor($user, $this->program, 'approved');

        Sanctum::actingAs($user);
        $this->postJson('/api/applications', ['program_id' => $this->program->id])->assertStatus(409)->assertJsonPath('code', 'already_applied');
        $this->actingAs($admin)->post(route('admin.participants.applications.store', $user->id), ['program_id' => $this->program->id])->assertSessionHasErrors('program_id');

        $application->update(['status' => 'rejected']);
        Sanctum::actingAs($user);
        $this->postJson('/api/applications', ['program_id' => $this->program->id])->assertStatus(201);

        // Otro participante no puede borrar postulaciones ajenas vía URL cruzada
        $other = $this->participant();
        $this->actingAs($admin)->delete(route('admin.participants.applications.destroy', [$other->id, $application->id]))->assertNotFound();
    }
}
