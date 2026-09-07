<?php

namespace Tests\Feature\ProgramEngine;

use App\Models\Application;
use App\Models\Program;
use App\Models\ProgramChecklistItem;
use App\Models\ProgramDocument;
use App\Models\ProgramDocumentRequirement;
use App\Models\ProgramPaymentGate;
use App\Models\ProgramProcess;
use App\Models\ProgramStage;
use App\Models\User;
use App\Services\ProgramEngine\ProcessResolver;
use App\Services\ProgramEngine\ProgramDefinition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class EngineTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        ProgramDefinition::forget();
    }

    protected function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    protected function participant(): User
    {
        return User::factory()->create(['role' => 'user']);
    }

    /** Programa mínimo del motor con 3 etapas (admission → application → completed). */
    protected function engineProgram(array $overrides = []): Program
    {
        $program = Program::create(array_merge([
            'name' => 'Programa Test',
            'slug' => 'programa-test',
            'description' => 'Test',
            'country' => 'USA',
            'main_category' => 'IE',
            'subcategory' => 'Test Program',
            'is_active' => true,
            'engine_enabled' => true,
            'is_available_in_app' => true,
            'modules' => ['english_test', 'resources'],
            'rules' => ['min_english_level' => 'B1', 'max_english_attempts' => 3],
        ], $overrides));

        ProgramStage::create(['program_id' => $program->id, 'key' => 'admission', 'label' => 'Admisión', 'sort_order' => 1, 'guards' => ['require_docs_approved' => true]]);
        ProgramStage::create(['program_id' => $program->id, 'key' => 'application', 'label' => 'Aplicación', 'sort_order' => 2, 'guards' => ['require_docs_approved' => true, 'require_gates' => ['inscription'], 'require_checklist' => ['contract_signed']]]);
        ProgramStage::create(['program_id' => $program->id, 'key' => 'completed', 'label' => 'Completado', 'sort_order' => 3, 'is_terminal' => true]);

        ProgramPaymentGate::create(['program_id' => $program->id, 'key' => 'inscription', 'label' => 'Pago de inscripción', 'sort_order' => 1]);
        ProgramChecklistItem::create(['program_id' => $program->id, 'stage_key' => 'application', 'key' => 'contract_signed', 'label' => 'Contrato firmado', 'item_type' => 'file', 'sort_order' => 1]);

        ProgramDocumentRequirement::create(['program_id' => $program->id, 'stage_key' => 'admission', 'key' => 'cedula', 'label' => 'Cédula', 'is_required' => true, 'sort_order' => 1]);
        ProgramDocumentRequirement::create(['program_id' => $program->id, 'stage_key' => 'admission', 'key' => 'passport', 'label' => 'Pasaporte', 'is_required' => false, 'sort_order' => 2]);
        ProgramDocumentRequirement::create(['program_id' => $program->id, 'stage_key' => 'application', 'key' => 'cv', 'label' => 'Curriculum', 'is_required' => true, 'unlock_gate_key' => 'inscription', 'sort_order' => 1]);
        ProgramDocumentRequirement::create(['program_id' => $program->id, 'stage_key' => 'application', 'key' => 'refs', 'label' => 'Referencias', 'is_required' => true, 'min_count' => 2, 'unlock_gate_key' => 'inscription', 'sort_order' => 2]);

        ProgramDefinition::forget($program);

        return $program;
    }

    protected function applicationFor(User $user, Program $program, string $status = 'approved'): Application
    {
        return Application::create([
            'user_id' => $user->id,
            'program_id' => $program->id,
            'status' => $status,
            'applied_at' => now(),
        ]);
    }

    protected function processFor(User $user, Program $program, string $status = 'approved'): ProgramProcess
    {
        return app(ProcessResolver::class)->forApplication($this->applicationFor($user, $program, $status));
    }

    protected function approvedDoc(ProgramProcess $process, string $key, string $stage, int $count = 1): void
    {
        for ($i = 0; $i < $count; $i++) {
            ProgramDocument::create([
                'program_process_id' => $process->id,
                'requirement_key' => $key,
                'stage_key' => $stage,
                'uploaded_by_type' => 'participant',
                'file_path' => "test/{$key}-{$i}.pdf",
                'original_filename' => "{$key}.pdf",
                'file_size' => 10,
                'status' => 'approved',
            ]);
        }
    }
}
