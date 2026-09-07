<?php

namespace Tests\Feature\ProgramEngine;

use App\Models\ProgramDocument;
use App\Services\ProgramEngine\ProgressCalculator;
use App\Services\ProgramEngine\StageEvaluator;

class StageEvaluatorTest extends EngineTestCase
{
    public function test_process_is_initialized_at_first_stage_with_gate_and_checklist_rows(): void
    {
        $program = $this->engineProgram();
        $process = $this->processFor($this->participant(), $program);

        $this->assertSame('admission', $process->current_stage_key);
        $this->assertSame('in_progress', $process->stageStatus('admission'));
        $this->assertSame('locked', $process->stageStatus('application'));
        $this->assertDatabaseHas('program_process_gates', ['program_process_id' => $process->id, 'gate_key' => 'inscription', 'is_verified' => 0]);
        $this->assertDatabaseHas('program_process_checklist', ['program_process_id' => $process->id, 'item_key' => 'contract_signed', 'is_done' => 0]);
    }

    public function test_cannot_advance_until_required_admission_docs_are_approved(): void
    {
        $program = $this->engineProgram();
        $process = $this->processFor($this->participant(), $program);
        $evaluator = new StageEvaluator;

        $this->assertFalse($evaluator->canAdvance($process));
        $this->assertStringContainsString('documentos requeridos', $evaluator->blockingReasons($process)[0]);

        // Documento pendiente no cuenta.
        ProgramDocument::create([
            'program_process_id' => $process->id, 'requirement_key' => 'cedula', 'stage_key' => 'admission',
            'file_path' => 'x.pdf', 'original_filename' => 'x.pdf', 'status' => 'pending',
        ]);
        $this->assertFalse($evaluator->canAdvance($process));

        // Opcional (passport) no es necesario; aprobar cédula alcanza.
        $this->approvedDoc($process, 'cedula', 'admission');
        $this->assertTrue($evaluator->canAdvance($process));
    }

    public function test_application_stage_requires_gate_checklist_and_min_count_docs(): void
    {
        $program = $this->engineProgram();
        $process = $this->processFor($this->participant(), $program);
        $process->update(['current_stage_key' => 'application']);
        $process->refresh();
        $evaluator = new StageEvaluator;

        $reasons = $evaluator->blockingReasons($process);
        $this->assertCount(3, $reasons);
        $this->assertStringContainsString('Pago de inscripción', implode(' ', $reasons));
        $this->assertStringContainsString('Contrato firmado', implode(' ', $reasons));

        $process->gates()->where('gate_key', 'inscription')->update(['is_verified' => true]);
        $process->checklist()->where('item_key', 'contract_signed')->update(['is_done' => true]);
        $this->approvedDoc($process, 'cv', 'application');
        $this->approvedDoc($process, 'refs', 'application', 1); // min_count = 2
        $process->unsetRelation('gates')->unsetRelation('checklist');

        $this->assertFalse($evaluator->canAdvance($process), 'refs necesita 2 aprobados');

        $this->approvedDoc($process, 'refs', 'application', 1);
        $this->assertTrue($evaluator->canAdvance($process));
    }

    public function test_progress_grows_within_stage_and_across_stages(): void
    {
        $program = $this->engineProgram();
        $process = $this->processFor($this->participant(), $program);
        $calc = new ProgressCalculator;

        $this->assertSame(0, $calc->percent($process));
        $this->approvedDoc($process, 'cedula', 'admission');
        $this->assertSame(50, $calc->percent($process)); // 1 requisito requerido de 1 en la primera de 2 etapas de workflow

        $process->update(['current_stage_key' => 'application']);
        $process->refresh();
        $this->assertSame(50, $calc->percent($process));

        $process->update(['status' => 'completed']);
        $this->assertSame(100, $calc->percent($process->fresh()));
    }
}
