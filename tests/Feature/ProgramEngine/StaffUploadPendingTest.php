<?php

namespace Tests\Feature\ProgramEngine;

use App\Models\Program;
use App\Services\ProgramEngine\StageEvaluator;
use Database\Seeders\WorkTravelProgramSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/** Lo que sube IE desde el admin también queda pendiente hasta aprobarlo (como en Au Pair). */
class StaffUploadPendingTest extends EngineTestCase
{
    private Program $program;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WorkTravelProgramSeeder::class);
        $this->program = Program::where('slug', 'work-travel')->firstOrFail();
        Storage::fake('public');
    }

    public function test_staff_upload_is_pending_and_blocks_advance_until_approved(): void
    {
        $admin = $this->admin();
        $process = $this->processFor($this->participant(), $this->program);
        $url = fn (string $name, array $extra = []) => route("admin.program.{$name}", array_merge(['program' => $this->program->slug, 'process' => $process->id], $extra));

        foreach (['cedula', 'enrollment_form'] as $key) {
            $this->actingAs($admin)->post($url('documents.upload'), ['requirement_key' => $key, 'files' => [UploadedFile::fake()->create("{$key}.pdf", 10, 'application/pdf')]])->assertRedirect();
        }

        $docs = $process->documents()->get();
        $this->assertCount(2, $docs);
        $this->assertTrue($docs->every(fn ($d) => $d->status === 'pending' && $d->reviewed_by === null && $d->uploaded_by_type === 'staff'));

        $evaluator = app(StageEvaluator::class);
        $stage = $this->program->stages()->where('key', 'admission')->firstOrFail();
        $this->assertNotEmpty($evaluator->blockingReasons($process->fresh(), $stage));

        // El admin ve los botones de aprobar en el hub
        $this->actingAs($admin)->get($url('participants.show', ['tab' => 'admission']))->assertOk()->assertSee('title="Aprobar"', false);

        $this->actingAs($admin)->put($url('documents.review', ['document' => $docs[0]->id]), ['action' => 'approve'])->assertRedirect();
        $this->actingAs($admin)->post($url('documents.bulk-approve', ['requirementKey' => 'enrollment_form']))->assertRedirect();

        $this->assertSame(2, $process->documents()->where('status', 'approved')->whereNotNull('reviewed_by')->count());
        $this->assertEmpty($evaluator->blockingReasons($process->fresh(), $stage));
    }
}
