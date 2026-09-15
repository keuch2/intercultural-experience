<?php

namespace Tests\Feature\ProgramEngine;

use App\Models\Program;
use Database\Seeders\WorkTravelProgramSeeder;
use Laravel\Sanctum\Sanctum;

/** Fecha de inicio/fin del programa a nivel del proceso: se edita en Datos personales, se sincroniza desde Job Placement y llega a la app. */
class ProgramDatesTest extends EngineTestCase
{
    private Program $program;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WorkTravelProgramSeeder::class);
        $this->program = Program::where('slug', 'work-travel')->firstOrFail();
    }

    public function test_personal_data_sets_clears_and_exposes_program_dates(): void
    {
        $admin = $this->admin();
        $process = $this->processFor($this->participant(), $this->program);
        $url = fn (string $name, array $extra = []) => route("admin.program.{$name}", array_merge(['program' => $this->program->slug, 'process' => $process->id], $extra));
        $base = ['name' => $process->user->name, 'enrollment_date' => '2026-03-01', 'season' => '2026'];

        $this->actingAs($admin)->put($url('participants.update-personal'), $base + ['program_start_date' => '2026-06-15', 'program_end_date' => '2026-09-15'])->assertSessionHasNoErrors();
        $fresh = $process->fresh();
        $this->assertSame('2026-06-15', $fresh->program_start_date->toDateString());
        $this->assertSame('2026-09-15', $fresh->program_end_date->toDateString());

        // Fin anterior al inicio → error de validación
        $this->actingAs($admin)->put($url('participants.update-personal'), $base + ['program_start_date' => '2026-06-15', 'program_end_date' => '2026-06-01'])->assertSessionHasErrors('program_end_date');

        // Hub y datos personales muestran la fecha
        $this->actingAs($admin)->get($url('participants.show', ['tab' => 'admission']))->assertOk()->assertSee('Inicio del programa')->assertSee('15/06/2026');

        // App
        Sanctum::actingAs($process->user);
        $this->getJson('/api/programs/work-travel/process')->assertOk()
            ->assertJsonPath('data.program_start_date', '2026-06-15')->assertJsonPath('data.program_end_date', '2026-09-15');

        // Vaciar (array_filter impedía limpiar)
        $this->actingAs($admin)->put($url('participants.update-personal'), $base + ['program_start_date' => '', 'program_end_date' => ''])->assertSessionHasNoErrors();
        $this->assertNull($process->fresh()->program_start_date);
        $this->assertNull($process->fresh()->program_end_date);
    }

    public function test_job_placement_dates_sync_to_the_process(): void
    {
        $admin = $this->admin();
        $process = $this->processFor($this->participant(), $this->program);
        $process->update(['current_stage_key' => 'placement']);

        $this->actingAs($admin)->put(route('admin.program.placement.update', [$this->program->slug, $process->id]), [
            'program_start_date' => '2026-06-20', 'program_end_date' => '2026-09-20',
        ])->assertSessionHasNoErrors();

        $fresh = $process->fresh();
        $this->assertSame('2026-06-20', $fresh->program_start_date->toDateString());
        $this->assertSame('2026-09-20', $fresh->program_end_date->toDateString());
        $this->assertSame('2026-06-20', $fresh->placement->program_start_date->toDateString());
    }
}
