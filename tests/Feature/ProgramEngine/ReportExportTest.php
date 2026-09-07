<?php

namespace Tests\Feature\ProgramEngine;

use App\Models\Program;
use App\Models\Sponsor;
use App\Services\ProgramEngine\JobPoolService;
use App\Services\ProgramEngine\PlacementService;
use Database\Seeders\WorkTravelProgramSeeder;

class ReportExportTest extends EngineTestCase
{
    private Program $program;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WorkTravelProgramSeeder::class);
        $this->program = Program::where('slug', 'work-travel')->firstOrFail();
    }

    public function test_index_and_csv_export_apply_spec_filters(): void
    {
        $admin = $this->admin();

        $ana = $this->participant();
        $ana->update(['name' => 'Ana Filtrada', 'university' => 'UNA', 'career' => 'Ingeniería']);
        $pAna = $this->processFor($ana, $this->program);
        $pAna->update(['season' => '2026', 'module_access' => ['job_pool' => ['enabled' => true]]]);
        $this->approvedDoc($pAna, 'cedula', 'admission');
        $this->approvedDoc($pAna, 'enrollment_form', 'admission');
        $pAna->englishTests()->create(['evaluator_name' => 'x', 'exam_name' => 'EF', 'final_score' => 60, 'cefr_level' => 'B2', 'attempt_number' => 1]);
        $pAna->gates()->where('gate_key', 'inscription')->update(['is_verified' => true]);
        $offer = app(JobPoolService::class)->publish($this->program, ['employer_name' => 'Busch Gardens', 'state' => 'Florida', 'city' => 'Tampa', 'positions_total' => 2], null, $admin);
        app(JobPoolService::class)->select($offer, $pAna->fresh());
        app(PlacementService::class)->update($pAna->fresh(), ['sponsor_id' => Sponsor::where('code', 'AWA')->first()->id]);
        $pAna->visaProcess()->create(['interview_result' => 'approved', 'departure_datetime' => '2026-06-01 10:00:00']);

        $beto = $this->participant();
        $beto->update(['name' => 'Beto Otro', 'university' => 'UCA', 'career' => 'Derecho']);
        $pBeto = $this->processFor($beto, $this->program);
        $pBeto->update(['season' => '2027']);

        $slug = $this->program->slug;
        $this->actingAs($admin)->get(route('admin.program.reports.index', $slug))->assertOk()->assertSee('Ana Filtrada')->assertSee('Beto Otro');

        $filters = [
            ['season' => '2026'], ['university' => 'UNA'], ['career' => 'Ingen'], ['doc_status' => 'complete'], ['english_level' => 'B2'],
            ['offer_id' => $offer->id], ['sponsor_id' => Sponsor::where('code', 'AWA')->first()->id], ['visa_result' => 'approved'],
            ['travel' => 'scheduled'], ['gate' => 'inscription', 'gate_verified' => '1'],
        ];
        foreach ($filters as $filter) {
            $res = $this->actingAs($admin)->get(route('admin.program.reports.export', array_merge(['program' => $slug], $filter)));
            $res->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
            $csv = $res->getContent();
            $this->assertStringContainsString('Ana Filtrada', $csv, 'filtro '.json_encode($filter));
            $this->assertStringNotContainsString('Beto Otro', $csv, 'filtro '.json_encode($filter));
        }

        $csv = $this->actingAs($admin)->get(route('admin.program.reports.export', ['program' => $slug, 'doc_status' => 'missing']))->getContent();
        $this->assertStringContainsString('Beto Otro', $csv);
        $this->assertStringNotContainsString('Ana Filtrada', $csv);

        $full = $this->actingAs($admin)->get(route('admin.program.reports.export', $slug))->getContent();
        $this->assertStringContainsString('Busch Gardens', $full);
        $this->assertStringContainsString('AWA', $full);
        $this->assertStringContainsString('Pago de inscripción', $full);
        $this->assertSame(3, substr_count($full, "\n"), 'cabecera + 2 filas');
    }

    public function test_legacy_work_travel_routes_redirect_to_engine_hub(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->get('/admin/work-travel/dashboard')->assertRedirect(route('admin.program.participants.index', 'work-travel'));
        $this->actingAs($admin)->get('/admin/work-travel/employers')->assertRedirect(route('admin.program.job-pool.index', 'work-travel'));
        $this->actingAs($admin)->get(route('admin.work-travel.matching'))->assertRedirect();
    }

    public function test_reports_require_engine_program(): void
    {
        $plain = Program::create(['name' => 'Plain', 'slug' => 'plain', 'description' => 'x', 'country' => 'USA', 'main_category' => 'IE', 'subcategory' => 'Plain', 'is_active' => true]);
        $this->actingAs($this->admin())->get(route('admin.program.reports.index', $plain->slug))->assertNotFound();
    }
}
