<?php

namespace Tests\Feature\ProgramEngine;

use App\Models\Program;
use Database\Seeders\WorkTravelProgramSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProcessHubAdminTest extends EngineTestCase
{
    private Program $program;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed(WorkTravelProgramSeeder::class);
        $this->program = Program::where('slug', 'work-travel')->firstOrFail();
    }

    public function test_index_lists_processes_and_every_tab_renders(): void
    {
        $admin = $this->admin();
        $process = $this->processFor($this->participant(), $this->program);

        $this->actingAs($admin)->get(route('admin.program.participants.index', $this->program->slug))
            ->assertOk()->assertSee($process->user->name);

        $tabs = ['admission', 'application', 'job_pool', 'placement', 'visa', 'support', 'payments', 'resources', 'reports'];
        foreach ($tabs as $tab) {
            $this->actingAs($admin)
                ->get(route('admin.program.participants.show', ['program' => $this->program->slug, 'process' => $process->id, 'tab' => $tab]))
                ->assertOk();
        }
    }

    public function test_non_engine_program_hub_is_not_found(): void
    {
        $plain = Program::create(['name' => 'Plain', 'slug' => 'plain', 'description' => 'x', 'country' => 'USA', 'main_category' => 'IE', 'subcategory' => 'Plain', 'is_active' => true]);
        $this->actingAs($this->admin())->get(route('admin.program.participants.index', $plain->slug))->assertNotFound();
    }

    public function test_full_admission_flow_from_admin(): void
    {
        $admin = $this->admin();
        $process = $this->processFor($this->participant(), $this->program, 'pending');
        $url = fn (string $name, array $extra = []) => route("admin.program.{$name}", array_merge(['program' => $this->program->slug, 'process' => $process->id], $extra));

        // Aprobar postulante
        $this->actingAs($admin)->post($url('participants.approve'))->assertRedirect();
        $this->assertSame('approved', $process->application->fresh()->status);

        // Subir docs de admisión desde el admin (quedan aprobados)
        foreach (['cedula', 'enrollment_form'] as $key) {
            $this->actingAs($admin)->post($url('documents.upload'), [
                'requirement_key' => $key, 'files' => [UploadedFile::fake()->create("{$key}.pdf", 100, 'application/pdf')],
            ])->assertRedirect();
        }
        $this->assertDatabaseCount('program_documents', 2);
        $this->assertSame(2, $process->documents()->where('status', 'approved')->count());

        // Avanzar a aplicación
        $this->actingAs($admin)->post($url('stage.advance'))->assertRedirect()->assertSessionHas('success');
        $this->assertSame('application', $process->fresh()->current_stage_key);

        // Avance bloqueado: falta gate, contrato e inglés
        $this->actingAs($admin)->post($url('stage.advance'))->assertSessionHas('error');

        // Verificar gate, subir contrato (checklist file), registrar inglés B1, subir docs de aplicación
        $this->actingAs($admin)->put($url('gates.update', ['gateKey' => 'inscription']), ['value' => 1])->assertRedirect();
        $this->actingAs($admin)->put($url('checklist.update'), [
            'stage_key' => 'application', 'items' => ['welcome_email_sent' => 1, 'sponsor_profile_created' => 0],
            'files' => ['contract_signed' => UploadedFile::fake()->create('contrato.pdf', 50, 'application/pdf')],
        ])->assertRedirect();
        $this->assertDatabaseHas('program_process_checklist', ['program_process_id' => $process->id, 'item_key' => 'contract_signed', 'is_done' => 1]);
        $this->assertDatabaseHas('program_process_checklist', ['program_process_id' => $process->id, 'item_key' => 'welcome_email_sent', 'is_done' => 1]);

        $this->actingAs($admin)->post($url('english.store'), ['evaluator_name' => 'Ana', 'exam_name' => 'EF SET', 'final_score' => 45])->assertRedirect();
        $this->assertDatabaseHas('program_english_tests', ['program_process_id' => $process->id, 'cefr_level' => 'B1', 'attempt_number' => 1]);

        foreach (['university_certificate', 'grades_certificate', 'curriculum', 'signed_contract'] as $key) {
            $this->actingAs($admin)->post($url('documents.upload'), ['requirement_key' => $key, 'files' => [UploadedFile::fake()->create("{$key}.pdf", 10)]])->assertRedirect();
        }

        $this->actingAs($admin)->post($url('stage.advance'))->assertSessionHas('success');
        $this->assertSame('job_pool', $process->fresh()->current_stage_key);

        // Habilitar acceso al pool y verificar módulo en el envelope vía hub
        $this->actingAs($admin)->put($url('module-access.update'), ['module' => 'job_pool', 'enabled' => 1])->assertRedirect();
        $this->assertTrue($process->fresh()->hasModuleAccess('job_pool'));

        // Retroceder
        $this->actingAs($admin)->post($url('stage.revert'), ['to_stage' => 'application', 'reason' => 'test'])->assertRedirect();
        $this->assertSame('application', $process->fresh()->current_stage_key);

        // Finalizar → terminal
        $this->actingAs($admin)->put($url('finalization.update'), ['finalization_result' => 'success'])->assertRedirect();
        $fresh = $process->fresh();
        $this->assertSame('completed', $fresh->status);
        $this->assertSame('completed', $fresh->current_stage_key);
        $this->assertDatabaseHas('notifications', ['user_id' => $process->user_id, 'category' => 'program_stage']);
    }

    public function test_document_review_reject_and_delete(): void
    {
        $admin = $this->admin();
        $process = $this->processFor($this->participant(), $this->program);
        $doc = $process->documents()->create([
            'requirement_key' => 'cedula', 'stage_key' => 'admission', 'uploaded_by_type' => 'participant',
            'file_path' => 'x.pdf', 'original_filename' => 'x.pdf', 'file_size' => 1, 'status' => 'pending',
        ]);
        $url = fn (string $name, array $extra = []) => route("admin.program.{$name}", array_merge(['program' => $this->program->slug, 'process' => $process->id], $extra));

        $this->actingAs($admin)->put($url('documents.review', ['document' => $doc->id]), ['action' => 'reject'])->assertSessionHas('error');
        $this->actingAs($admin)->put($url('documents.review', ['document' => $doc->id]), ['action' => 'reject', 'rejection_reason' => 'Ilegible'])->assertSessionHas('success');
        $this->assertSame('rejected', $doc->fresh()->status);

        $this->actingAs($admin)->put($url('documents.review', ['document' => $doc->id]), ['action' => 'approve'])->assertSessionHas('success');
        $this->assertSame('approved', $doc->fresh()->status);

        $this->actingAs($admin)->delete($url('documents.delete', ['document' => $doc->id]))->assertSessionHas('error');
        $this->actingAs($admin)->delete($url('documents.delete', ['document' => $doc->id]), ['deletion_reason' => 'Duplicado'])->assertSessionHas('success');
        $this->assertSoftDeleted('program_documents', ['id' => $doc->id]);
    }

    public function test_process_from_other_program_is_not_accessible_under_this_slug(): void
    {
        $other = $this->engineProgram();
        $process = $this->processFor($this->participant(), $other);

        $this->actingAs($this->admin())
            ->get(route('admin.program.participants.show', ['program' => $this->program->slug, 'process' => $process->id]))
            ->assertNotFound();
    }
}
