<?php

namespace Tests\Feature\ProgramEngine;

use App\Models\JobPoolOffer;
use App\Models\Program;
use App\Services\ProgramEngine\JobPoolService;
use Database\Seeders\WorkTravelProgramSeeder;
use Illuminate\Http\UploadedFile;

/**
 * Puesto laboral y fecha límite para postular en el pool de ofertas:
 * obligatorios en el admin, expuestos por la API y la oferta vencida deja de
 * mostrarse / seleccionarse desde la app (el staff sí puede asignarla).
 */
class JobPoolTitleAndDeadlineTest extends EngineTestCase
{
    private Program $program;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WorkTravelProgramSeeder::class);
        $this->program = Program::where('slug', 'work-travel')->firstOrFail();
    }

    private function publish(array $overrides = []): JobPoolOffer
    {
        return app(JobPoolService::class)->publish($this->program, array_merge([
            'job_title' => 'Lifeguard', 'employer_name' => 'Wet n Wild', 'state' => 'Florida', 'city' => 'Orlando',
            'positions_total' => 2, 'application_deadline' => now()->addDays(10)->toDateString(),
        ], $overrides), null, $this->admin());
    }

    public function test_admin_requires_job_title_and_deadline_and_rejects_past_deadline_on_create(): void
    {
        $admin = $this->admin();
        $slug = $this->program->slug;
        $base = ['employer_name' => 'Wet n Wild', 'state' => 'Florida', 'city' => 'Orlando', 'positions_total' => 1,
            'pdf' => UploadedFile::fake()->create('o.pdf', 10, 'application/pdf')];

        $this->actingAs($admin)->from(route('admin.program.job-pool.create', $slug))
            ->post(route('admin.program.job-pool.store', $slug), $base)
            ->assertSessionHasErrors(['job_title', 'application_deadline']);

        $this->actingAs($admin)->from(route('admin.program.job-pool.create', $slug))
            ->post(route('admin.program.job-pool.store', $slug), $base + ['job_title' => 'Lifeguard', 'application_deadline' => now()->subDay()->toDateString()])
            ->assertSessionHasErrors(['application_deadline']);

        $this->actingAs($admin)->post(route('admin.program.job-pool.store', $slug), $base + ['job_title' => 'Lifeguard', 'application_deadline' => now()->addWeek()->toDateString()])
            ->assertRedirect();

        $offer = JobPoolOffer::firstOrFail();
        $this->assertSame('Lifeguard', $offer->job_title);
        $this->assertSame(now()->addWeek()->toDateString(), $offer->application_deadline->toDateString());

        // El listado y la ficha muestran puesto y fecha límite
        $this->actingAs($admin)->get(route('admin.program.job-pool.index', $slug))->assertOk()
            ->assertSee('Lifeguard')->assertSee('Wet n Wild')->assertSee(now()->addWeek()->format('d/m/Y'));
        $this->actingAs($admin)->get(route('admin.program.job-pool.show', [$slug, $offer->id]))->assertOk()
            ->assertSee('Lifeguard')->assertSee(now()->addWeek()->format('d/m/Y'));
    }

    public function test_api_exposes_title_and_deadline_and_hides_expired_offers(): void
    {
        $open = $this->publish();
        $expired = $this->publish(['job_title' => 'Housekeeper', 'application_deadline' => now()->subDay()->toDateString()]);
        $this->assertSame('Vencida', $expired->fresh()->status_label);

        $process = $this->processFor($this->participant(), $this->program);
        $process->update(['module_access' => ['job_pool' => ['enabled' => true]]]);

        $res = $this->actingAs($process->user)->getJson(route('api.programs.job-pool.offers', $this->program->slug))->assertOk();
        $ids = collect($res->json('data'))->pluck('id')->all();
        $this->assertSame([$open->id], $ids, 'La oferta vencida no debe listarse en la app');

        $row = collect($res->json('data'))->firstWhere('id', $open->id);
        $this->assertSame('Lifeguard', $row['job_title']);
        $this->assertSame($open->application_deadline->toDateString(), $row['application_deadline']);
        $this->assertFalse($row['deadline_passed']);

        // Seleccionar la vencida desde la app se rechaza; el staff sí puede asignarla
        $this->actingAs($process->user)->postJson(route('api.programs.job-pool.select', [$this->program->slug, $expired->id]), ['confirm' => true])
            ->assertStatus(409)->assertJsonPath('code', 'deadline_passed');

        $assignment = app(JobPoolService::class)->select($expired, $process, $this->admin());
        $this->assertSame($expired->id, $assignment->job_pool_offer_id);
    }

    public function test_offers_without_title_or_deadline_keep_working(): void
    {
        $legacy = app(JobPoolService::class)->publish($this->program, ['employer_name' => 'Old Employer', 'state' => 'Ohio', 'city' => 'Mason', 'positions_total' => 1], null, $this->admin());
        $this->assertNull($legacy->job_title);
        $this->assertNull($legacy->application_deadline);
        $this->assertSame('Old Employer', $legacy->display_name);
        $this->assertTrue($legacy->isSelectable());
        $this->assertSame('Activa', $legacy->status_label);
    }

    public function test_requirements_and_flyer_are_stored_and_exposed_with_position_as_headline(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $admin = $this->admin();
        $slug = $this->program->slug;

        $this->actingAs($admin)->post(route('admin.program.job-pool.store', $slug), [
            'job_title' => 'Mesero', 'requirements' => "Inglés B1+\nMayor de 18 años", 'employer_name' => 'Hotel Aspen',
            'state' => 'Colorado', 'city' => 'Aspen', 'positions_total' => 1, 'application_deadline' => now()->addWeek()->toDateString(),
            'pdf' => UploadedFile::fake()->create('o.pdf', 10, 'application/pdf'),
            'image' => UploadedFile::fake()->image('flyer.jpg', 800, 600),
        ])->assertRedirect();

        $offer = JobPoolOffer::firstOrFail();
        $this->assertSame("Inglés B1+\nMayor de 18 años", $offer->requirements);
        $this->assertTrue($offer->hasImage());
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($offer->image_path);
        $this->assertStringContainsString('storage/job-pool/', $offer->image_url);

        // La ficha lidera con el puesto y muestra requisitos y flyer
        $this->actingAs($admin)->get(route('admin.program.job-pool.show', [$slug, $offer->id]))->assertOk()
            ->assertSeeInOrder(['Mesero', 'Hotel Aspen'])->assertSee('Requisitos del puesto')->assertSee($offer->image_url, false);

        // La API expone requisitos e imagen
        $process = $this->processFor($this->participant(), $this->program);
        $process->update(['module_access' => ['job_pool' => ['enabled' => true]]]);
        $row = collect($this->actingAs($process->user)->getJson(route('api.programs.job-pool.offers', $slug))->assertOk()->json('data'))->firstWhere('id', $offer->id);
        $this->assertSame('Mesero', $row['job_title']);
        $this->assertSame("Inglés B1+\nMayor de 18 años", $row['requirements']);
        $this->assertSame($offer->image_url, $row['image_url']);

        // Un archivo que no es imagen se rechaza
        $this->actingAs($admin)->from(route('admin.program.job-pool.edit', [$slug, $offer->id]))
            ->put(route('admin.program.job-pool.update', [$slug, $offer->id]), [
                'job_title' => 'Mesero', 'employer_name' => 'Hotel Aspen', 'state' => 'Colorado', 'city' => 'Aspen', 'positions_total' => 1,
                'application_deadline' => now()->addWeek()->toDateString(), 'image' => UploadedFile::fake()->create('x.exe', 10, 'application/octet-stream'),
            ])->assertSessionHasErrors('image');
    }
}
