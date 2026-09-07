<?php

namespace Tests\Feature\ProgramEngine;

use App\Models\Program;
use App\Models\User;
use Database\Seeders\WorkTravelProgramSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

class ProcessApiTest extends EngineTestCase
{
    private Program $program;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed(WorkTravelProgramSeeder::class);
        $this->program = Program::where('slug', 'work-travel')->firstOrFail();
        $this->user = $this->participant();
        Sanctum::actingAs($this->user);
    }

    public function test_apply_from_app_creates_process_and_me_process_returns_envelope(): void
    {
        $this->getJson('/api/me/process')->assertStatus(404)->assertJsonPath('status', 'no_application');

        $this->postJson('/api/applications', ['program_id' => $this->program->id])->assertStatus(201);
        $this->assertDatabaseCount('program_processes', 1);

        $this->getJson('/api/me/process')->assertOk()
            ->assertJsonPath('data.program.slug', 'work-travel')
            ->assertJsonPath('data.current_stage', 'admission')
            ->assertJsonPath('data.application_approved', false)
            ->assertJsonPath('data.next_action.key', 'wait_approval');

        $this->getJson('/api/programs/work-travel/process')->assertOk()->assertJsonPath('data.current_stage', 'admission');
        $this->getJson("/api/programs/{$this->program->id}/process")->assertOk();
    }

    public function test_program_without_engine_is_not_bound(): void
    {
        $plain = Program::create(['name' => 'Plain', 'slug' => 'plain', 'description' => 'x', 'country' => 'USA', 'main_category' => 'IE', 'subcategory' => 'Plain', 'is_active' => true]);
        $this->getJson("/api/programs/{$plain->slug}/process")->assertNotFound();
    }

    public function test_documents_are_locked_until_approval_and_gate(): void
    {
        $process = $this->processFor($this->user, $this->program, 'pending');

        $this->getJson('/api/programs/work-travel/documents')->assertOk()->assertJsonPath('locked', true)->assertJsonPath('reason', 'pending_approval');
        $this->postJson('/api/programs/work-travel/documents', ['document_type' => 'cedula', 'files' => [UploadedFile::fake()->create('c.pdf', 10)]])
            ->assertStatus(403)->assertJsonPath('code', 'pending_approval');

        $process->application->update(['status' => 'approved']);

        // Admisión desbloqueada; aplicación bloqueada por etapa
        $items = collect($this->getJson('/api/programs/work-travel/documents')->assertOk()->json('data'));
        $this->assertTrue($items->firstWhere('document_type', 'cedula')['unlocked']);
        $this->assertFalse($items->firstWhere('document_type', 'curriculum')['unlocked']);
        $this->assertSame('stage_locked', $items->firstWhere('document_type', 'curriculum')['lock_reason']);

        // Subida OK (pending), duplicado pendiente → 409, staff-only → 403
        $this->postJson('/api/programs/work-travel/documents', ['document_type' => 'cedula', 'files' => [UploadedFile::fake()->create('c.pdf', 10)]])
            ->assertStatus(201)->assertJsonPath('data.0.status', 'pending');
        $this->postJson('/api/programs/work-travel/documents', ['document_type' => 'cedula', 'files' => [UploadedFile::fake()->create('c2.pdf', 10)]])
            ->assertStatus(409)->assertJsonPath('code', 'pending_exists');
        $this->postJson('/api/programs/work-travel/documents', ['document_type' => 'curriculum', 'files' => [UploadedFile::fake()->create('cv.pdf', 10)]])
            ->assertStatus(403)->assertJsonPath('code', 'locked');

        // Avanzar a aplicación con gate sin verificar → docs de aplicación bloqueados por gate
        $process->update(['current_stage_key' => 'application']);
        $items = collect($this->getJson('/api/programs/work-travel/documents?group=application')->json('data'));
        $this->assertSame('gate:inscription', $items->first()['lock_reason']);
        $this->getJson('/api/programs/work-travel/process')->assertJsonPath('data.next_action.key', 'pay_gate')->assertJsonPath('data.next_action.screen', 'Payments');

        $process->gates()->where('gate_key', 'inscription')->update(['is_verified' => true]);
        $this->postJson('/api/programs/work-travel/documents', ['document_type' => 'curriculum', 'files' => [UploadedFile::fake()->create('cv.pdf', 10)]])->assertStatus(201);
        $this->getJson('/api/programs/work-travel/process')->assertJsonPath('data.next_action.key', 'upload_docs');

        // Eliminar el propio pendiente; descargar
        $id = $process->documents()->where('requirement_key', 'cedula')->first()->id;
        $this->getJson("/api/programs/work-travel/documents/{$id}/download")->assertOk();
        $this->deleteJson("/api/programs/work-travel/documents/{$id}")->assertOk();
        $this->assertSoftDeleted('program_documents', ['id' => $id]);
    }

    public function test_english_visa_support_and_resources_endpoints(): void
    {
        $process = $this->processFor($this->user, $this->program);
        $process->englishTests()->create(['evaluator_name' => 'A', 'exam_name' => 'EF', 'final_score' => 55, 'cefr_level' => 'B2', 'attempt_number' => 1]);
        $process->supportLogs()->create(['log_type' => 'employer_change', 'title' => 'Cambio', 'log_date' => now()->toDateString()]);

        $this->getJson('/api/programs/work-travel/english-tests')->assertOk()
            ->assertJsonPath('data.max_attempts', 3)->assertJsonPath('data.remaining_attempts', 2)
            ->assertJsonPath('data.best_level', 'B2')->assertJsonPath('data.meets_minimum', true)
            ->assertJsonPath('data.tests.0.cefr_level', 'B2');
        $this->postJson('/api/programs/work-travel/english-tests', [])->assertStatus(403);

        $this->getJson('/api/programs/work-travel/visa-process')->assertOk()->assertJsonPath('data.has_visa_process', false);
        $process->visaProcess()->create(['visa_email_sent' => true, 'interview_result' => 'approved']);
        $this->getJson('/api/programs/work-travel/visa-process')->assertOk()
            ->assertJsonPath('data.has_visa_process', true)->assertJsonPath('data.interview.result_label', 'Aprobada')
            ->assertJsonCount(8, 'data.timeline');

        $this->getJson('/api/programs/work-travel/support-logs')->assertOk()->assertJsonPath('data.0.log_type_label', 'Cambio de empleador');

        $this->getJson('/api/programs/work-travel/resources')->assertOk()->assertJsonCount(8, 'data')->assertJsonPath('data.0.download_url', null);
    }

    public function test_user_cannot_see_another_users_process(): void
    {
        $other = $this->participant();
        $this->processFor($other, $this->program);

        $this->getJson('/api/programs/work-travel/process')->assertStatus(404)->assertJsonPath('status', 'no_application');
    }
}
