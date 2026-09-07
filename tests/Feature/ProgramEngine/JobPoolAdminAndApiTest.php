<?php

namespace Tests\Feature\ProgramEngine;

use App\Models\JobPoolOffer;
use App\Models\Program;
use App\Models\User;
use Database\Seeders\WorkTravelProgramSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

class JobPoolAdminAndApiTest extends EngineTestCase
{
    private Program $program;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed(WorkTravelProgramSeeder::class);
        $this->program = Program::where('slug', 'work-travel')->firstOrFail();
    }

    public function test_admin_crud_and_assignment_management(): void
    {
        $admin = $this->admin();
        $slug = $this->program->slug;

        $this->actingAs($admin)->get(route('admin.program.job-pool.index', $slug))->assertOk();
        $this->actingAs($admin)->get(route('admin.program.job-pool.create', $slug))->assertOk();

        $this->actingAs($admin)->post(route('admin.program.job-pool.store', $slug), [
            'employer_name' => 'Hershey Park', 'state' => 'Pennsylvania', 'city' => 'Hershey', 'positions_total' => 2,
            'pdf' => UploadedFile::fake()->create('oferta.pdf', 100, 'application/pdf'),
        ])->assertRedirect();
        $offer = JobPoolOffer::firstOrFail();
        $this->assertTrue($offer->hasPdf());

        $this->actingAs($admin)->get(route('admin.program.job-pool.show', [$slug, $offer->id]))->assertOk()->assertSee('Hershey Park');
        $this->actingAs($admin)->get(route('admin.program.job-pool.edit', [$slug, $offer->id]))->assertOk();
        $this->actingAs($admin)->put(route('admin.program.job-pool.update', [$slug, $offer->id]), ['employer_name' => 'Hershey Park Inc', 'state' => 'PA', 'city' => 'Hershey', 'positions_total' => 3])->assertRedirect();
        $this->assertSame(3, $offer->fresh()->positions_available);

        // asignación manual, liberar, reasignar
        $p1 = $this->processFor($this->participant(), $this->program);
        $p1->update(['module_access' => ['job_pool' => ['enabled' => true]]]);
        $p2 = $this->processFor($this->participant(), $this->program);
        $p2->update(['module_access' => ['job_pool' => ['enabled' => true]]]);

        $this->actingAs($admin)->post(route('admin.program.job-pool.assign', [$slug, $offer->id]), ['process_id' => $p1->id])->assertSessionHas('success');
        $assignment = $offer->activeAssignments()->first();
        $this->assertSame($p1->id, $assignment->program_process_id);

        $this->actingAs($admin)->post(route('admin.program.job-pool.reassign', [$slug, $offer->id, $assignment->id]), ['to_process_id' => $p2->id])->assertSessionHas('success');
        $this->assertSame($p2->id, $offer->activeAssignments()->first()->program_process_id);

        $current = $offer->activeAssignments()->first();
        $this->actingAs($admin)->post(route('admin.program.job-pool.release', [$slug, $offer->id, $current->id]), ['reason' => 'x'])->assertSessionHas('success');
        $this->assertSame(3, $offer->fresh()->positions_available);

        // hub tabs de pool y placement renderizan con el módulo (no la etapa genérica)
        $this->actingAs($admin)->get(route('admin.program.participants.show', ['program' => $slug, 'process' => $p1->id, 'tab' => 'job_pool']))->assertOk()->assertSee('Acceso habilitado')->assertSee('Deshabilitar acceso')->assertSee('Pool de Ofertas Laborales');
        $this->actingAs($admin)->get(route('admin.program.participants.show', ['program' => $slug, 'process' => $p1->id, 'tab' => 'placement']))->assertOk()->assertSee('Job Placement')->assertSee('Número SEVIS');
        $this->actingAs($admin)->put(route('admin.program.placement.update', [$slug, $p1->id]), ['sevis_number' => 'N1'])->assertRedirect();
        $this->assertDatabaseHas('job_placements', ['program_process_id' => $p1->id, 'sevis_number' => 'N1']);

        $this->actingAs($admin)->post(route('admin.program.job-pool.pause', [$slug, $offer->id]))->assertRedirect();
        $this->assertSame('paused', $offer->fresh()->status);
        $this->actingAs($admin)->post(route('admin.program.job-pool.close', [$slug, $offer->id]))->assertRedirect();
        $this->assertSame('closed', $offer->fresh()->status);
        $this->actingAs($admin)->delete(route('admin.program.job-pool.destroy', [$slug, $offer->id]))->assertRedirect(route('admin.program.job-pool.index', $slug));
        $this->assertSoftDeleted('job_pool_offers', ['id' => $offer->id]);
    }

    public function test_participant_api_flow_select_with_confirmation(): void
    {
        $user = $this->participant();
        Sanctum::actingAs($user);
        $process = $this->processFor($user, $this->program);
        $offer = app(\App\Services\ProgramEngine\JobPoolService::class)->publish($this->program, ['employer_name' => 'Kings Island', 'state' => 'Ohio', 'city' => 'Mason', 'positions_total' => 1], UploadedFile::fake()->create('o.pdf', 10, 'application/pdf'), $this->admin());

        $this->getJson('/api/programs/work-travel/job-pool/offers')->assertStatus(403)->assertJsonPath('code', 'not_enabled');

        $process->update(['module_access' => ['job_pool' => ['enabled' => true]]]);
        $this->getJson('/api/programs/work-travel/job-pool/offers')->assertOk()
            ->assertJsonPath('access', true)->assertJsonPath('my_assignment', null)
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.employer_name', 'Kings Island');
        $this->getJson("/api/programs/work-travel/job-pool/offers/{$offer->id}/pdf")->assertOk();

        $this->postJson("/api/programs/work-travel/job-pool/offers/{$offer->id}/select", [])->assertStatus(422)->assertJsonPath('code', 'confirmation_required');
        $this->postJson("/api/programs/work-travel/job-pool/offers/{$offer->id}/select", ['confirm' => true])->assertStatus(201)->assertJsonPath('data.offer.id', $offer->id);
        $this->postJson("/api/programs/work-travel/job-pool/offers/{$offer->id}/select", ['confirm' => true])->assertStatus(409)->assertJsonPath('code', 'already_assigned');

        $this->getJson('/api/programs/work-travel/job-pool/offers')->assertOk()->assertJsonCount(0, 'data')->assertJsonPath('my_assignment.offer.employer_name', 'Kings Island');
        $this->getJson('/api/programs/work-travel/job-pool/assignment')->assertOk()->assertJsonPath('data.status', 'active');
        $this->getJson('/api/programs/work-travel/placement')->assertOk()->assertJsonPath('data.has_assignment', true)->assertJsonPath('data.placement', null);
        $this->getJson('/api/programs/work-travel/process')->assertOk()->assertJsonPath('data.modules.job_pool.has_active_assignment', true);

        // Otro participante no ve el cupo agotado
        $other = $this->participant();
        $this->processFor($other, $this->program)->update(['module_access' => ['job_pool' => ['enabled' => true]]]);
        Sanctum::actingAs($other);
        $this->getJson('/api/programs/work-travel/job-pool/offers')->assertOk()->assertJsonCount(0, 'data');
        $this->postJson("/api/programs/work-travel/job-pool/offers/{$offer->id}/select", ['confirm' => true])->assertStatus(409)->assertJsonPath('code', 'no_positions');
    }

    public function test_notifications_api_for_mobile_bell(): void
    {
        $user = $this->participant();
        Sanctum::actingAs($user);
        \App\Models\Notification::create(['user_id' => $user->id, 'title' => 'A', 'message' => 'a', 'category' => 'job_pool', 'created_at' => now()]);
        \App\Models\Notification::create(['user_id' => $user->id, 'title' => 'B', 'message' => 'b', 'category' => 'general', 'is_read' => true, 'created_at' => now()]);
        \App\Models\Notification::create(['user_id' => User::factory()->create()->id, 'title' => 'Ajena', 'message' => 'x', 'category' => 'general', 'created_at' => now()]);

        $this->getJson('/api/notifications')->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('data.0.type', 'success');
        $this->getJson('/api/notifications?unread=true')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/notifications/unread-count')->assertOk()->assertJsonPath('count', 1);

        $id = \App\Models\Notification::where('title', 'A')->first()->id;
        $this->patchJson("/api/notifications/{$id}/read")->assertOk()->assertJsonPath('data.is_read', true);
        $this->getJson('/api/notifications/unread-count')->assertJsonPath('count', 0);
        $this->patchJson('/api/notifications/mark-all-read')->assertOk();
        $this->deleteJson("/api/notifications/{$id}")->assertOk();
        $this->assertDatabaseMissing('notifications', ['id' => $id]);

        $foreign = \App\Models\Notification::where('title', 'Ajena')->first()->id;
        $this->getJson("/api/notifications/{$foreign}")->assertNotFound();
    }
}
