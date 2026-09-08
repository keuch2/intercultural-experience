<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\AuPairDocument;
use App\Models\AuPairProcess;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Requisitos multi-archivo (12 fotos, referencias, certificaciones): una vez
 * aprobados y completos, el participante no puede seguir subiendo.
 */
class AuPairDocumentLockTest extends TestCase
{
    use RefreshDatabase;

    private AuPairProcess $process;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $program = Program::create(['name' => 'Au Pair USA', 'description' => 'x', 'country' => 'USA', 'main_category' => 'IE', 'subcategory' => 'Au Pair', 'is_active' => true]);
        $user = User::factory()->create(['role' => 'user']);
        $application = Application::create(['user_id' => $user->id, 'program_id' => $program->id, 'status' => 'approved', 'applied_at' => now()]);
        $this->process = AuPairProcess::create(['application_id' => $application->id, 'user_id' => $user->id, 'current_stage' => 'application', 'admission_status' => 'approved', 'application_status' => 'in_progress', 'match_visa_status' => 'locked', 'support_status' => 'locked']);
        Sanctum::actingAs($user);
    }

    private function approvedFiles(string $type, string $stage, int $n): void
    {
        for ($i = 0; $i < $n; $i++) {
            AuPairDocument::create(['au_pair_process_id' => $this->process->id, 'document_type' => $type, 'stage' => $stage, 'file_path' => "x{$i}.jpg", 'original_filename' => "x{$i}.jpg", 'file_size' => 1, 'status' => 'approved', 'min_count' => 1]);
        }
    }

    public function test_multi_file_requirement_locks_when_complete_and_approved(): void
    {
        // character_ref: min_count 2
        $this->approvedFiles('character_ref', 'application_payment2', 1);
        $this->postJson('/api/au-pair/documents', ['document_type' => 'character_ref', 'stage' => 'application_payment2', 'files' => [UploadedFile::fake()->create('ref2.pdf', 10, 'application/pdf')]])
            ->assertStatus(201);

        $items = collect($this->getJson('/api/au-pair/documents?stage=application_payment2')->json('data'));
        $entry = $items->firstWhere('document_type', 'character_ref');
        $this->assertSame('pending', $entry['status'], '1 aprobado + 1 pendiente de 2 → pending, no approved');
        $this->assertSame(1, $entry['approved_count']);

        $this->approvedFiles('character_ref', 'application_payment2', 1);
        $this->postJson('/api/au-pair/documents', ['document_type' => 'character_ref', 'stage' => 'application_payment2', 'files' => [UploadedFile::fake()->create('ref3.pdf', 10, 'application/pdf')]])
            ->assertStatus(403)->assertJsonPath('code', 'already_approved');

        $entry = collect($this->getJson('/api/au-pair/documents?stage=application_payment2')->json('data'))->firstWhere('document_type', 'character_ref');
        $this->assertSame('approved', $entry['status']);
    }

    public function test_allow_multiple_requirement_locks_once_any_file_is_approved(): void
    {
        // certifications: allow_multiple sin min_count → con 1 aprobado queda bloqueado
        $this->approvedFiles('certifications', 'application_payment1', 1);
        $this->postJson('/api/au-pair/documents', ['document_type' => 'certifications', 'stage' => 'application_payment1', 'files' => [UploadedFile::fake()->create('c.pdf', 10, 'application/pdf')]])
            ->assertStatus(403)->assertJsonPath('code', 'already_approved');
    }

    public function test_single_file_behaviour_unchanged_and_staff_docs_downloadable(): void
    {
        $this->approvedFiles('cedula', 'admission', 1);
        $this->postJson('/api/au-pair/documents', ['document_type' => 'cedula', 'stage' => 'admission', 'files' => [UploadedFile::fake()->create('c.pdf', 10, 'application/pdf')]])
            ->assertStatus(403)->assertJsonPath('code', 'already_approved');

        Storage::disk('public')->put('ds.pdf', 'pdf');
        $staff = AuPairDocument::create(['au_pair_process_id' => $this->process->id, 'document_type' => 'ds2019', 'stage' => 'visa', 'uploaded_by_type' => 'staff', 'file_path' => 'ds.pdf', 'original_filename' => 'DS-2019.pdf', 'file_size' => 3, 'status' => 'approved']);
        $this->getJson("/api/au-pair/documents/{$staff->id}/download")->assertOk();
        $entry = collect($this->getJson('/api/au-pair/documents?stage=visa')->json('data'))->firstWhere('document_type', 'ds2019');
        $this->assertNotEmpty($entry['files'][0]['download_url']);
    }
}
