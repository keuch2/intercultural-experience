<?php

namespace Tests\Feature\ProgramEngine;

use App\Models\JobPoolOffer;
use App\Models\Program;
use App\Models\Sponsor;
use App\Services\ProgramEngine\JobPoolService;
use App\Services\ProgramEngine\PlacementService;
use Database\Seeders\WorkTravelProgramSeeder;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;

/** Sponsor opcional en la oferta del Pool: admin, API, herencia al Job Placement y conteos en Sponsors. */
class JobPoolSponsorTest extends EngineTestCase
{
    private Program $program;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WorkTravelProgramSeeder::class);
        $this->program = Program::where('slug', 'work-travel')->firstOrFail();
    }

    public function test_offer_with_sponsor_is_listed_exposed_and_inherited_by_placement(): void
    {
        $admin = $this->admin();
        $sponsor = Sponsor::create(['name' => 'InterExchange', 'code' => 'IEX', 'country' => 'USA', 'is_active' => true]);
        $inactive = Sponsor::create(['name' => 'Old Sponsor', 'code' => 'OLD', 'country' => 'USA', 'is_active' => false]);
        $slug = $this->program->slug;

        // El formulario ofrece solo sponsors activos
        $this->actingAs($admin)->get(route('admin.program.job-pool.create', $slug))->assertOk()->assertSee('InterExchange (IEX)')->assertDontSee('Old Sponsor');

        $this->actingAs($admin)->post(route('admin.program.job-pool.store', $slug), [
            'job_title' => 'Lifeguard', 'employer_name' => 'Wet n Wild', 'state' => 'FL', 'city' => 'Orlando', 'positions_total' => 2,
            'application_deadline' => now()->addMonth()->toDateString(), 'sponsor_id' => $sponsor->id,
            'pdf' => UploadedFile::fake()->create('o.pdf', 10, 'application/pdf'),
        ])->assertRedirect();
        $offer = JobPoolOffer::firstOrFail();
        $this->assertSame($sponsor->id, $offer->sponsor_id);

        // Listado y ficha muestran el sponsor
        $this->actingAs($admin)->get(route('admin.program.job-pool.index', $slug))->assertOk()->assertSee('title="InterExchange"', false);
        $this->actingAs($admin)->get(route('admin.program.job-pool.show', [$slug, $offer->id]))->assertOk()->assertSee('InterExchange (IEX)');

        // Sin sponsor también es válido (opcional)
        $this->actingAs($admin)->put(route('admin.program.job-pool.update', [$slug, $offer->id]), [
            'job_title' => 'Lifeguard', 'employer_name' => 'Wet n Wild', 'state' => 'FL', 'city' => 'Orlando', 'positions_total' => 2,
            'application_deadline' => now()->addMonth()->toDateString(), 'sponsor_id' => '',
        ])->assertSessionHasNoErrors();
        $this->assertNull($offer->fresh()->sponsor_id);
        $offer = $offer->fresh();
        $offer->update(['sponsor_id' => $sponsor->id]);

        // API expone el sponsor
        $process = $this->processFor($this->participant(), $this->program);
        $process->update(['module_access' => ['job_pool' => ['enabled' => true]]]);
        Sanctum::actingAs($process->user);
        $this->getJson("/api/programs/{$slug}/job-pool/offers")->assertOk()->assertJsonPath('data.0.sponsor.code', 'IEX');

        // Al seleccionar la oferta, el Job Placement hereda el sponsor
        app(JobPoolService::class)->select($offer, $process, $admin);
        $placement = app(PlacementService::class)->ensure($process->fresh());
        $this->assertSame($sponsor->id, $placement->sponsor_id);

        // Sponsors: conteos de ofertas del Pool y participantes; con historial se desactiva en vez de borrar
        $this->actingAs($admin)->get(route('admin.sponsors.index'))->assertOk()->assertSee('1 ofertas del Pool')->assertSee('1 participantes');
        $this->actingAs($admin)->get(route('admin.sponsors.show', $sponsor->id))->assertOk()->assertSee('Ofertas del Pool (1)')->assertSee('Lifeguard');
        $this->actingAs($admin)->delete(route('admin.sponsors.destroy', $sponsor->id))->assertRedirect();
        $this->assertDatabaseHas('sponsors', ['id' => $sponsor->id, 'is_active' => 0]);
        $this->assertSame($sponsor->id, $offer->fresh()->sponsor_id, 'la oferta conserva el sponsor desactivado');
    }
}
