<?php

namespace Tests\Feature\ProgramEngine;

use App\Models\JobPlacement;
use App\Models\Program;
use App\Models\Sponsor;
use Database\Seeders\WorkTravelProgramSeeder;

/**
 * Informe del cliente (24/09): flash duplicado, notas de la postulación en el hub,
 * "Eliminar postulación" en el encabezado, fechas del programa en Visa y sponsors con baja lógica.
 */
class HubAdminFeedbackTest extends EngineTestCase
{
    private Program $program;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WorkTravelProgramSeeder::class);
        $this->program = Program::where('slug', 'work-travel')->firstOrFail();
    }

    public function test_hub_shows_flash_once_notes_widget_and_delete_button_in_header(): void
    {
        $admin = $this->admin();
        $process = $this->processFor($this->participant(), $this->program);
        $url = fn (string $name, array $extra = []) => route("admin.program.{$name}", array_merge(['program' => $this->program->slug, 'process' => $process->id], $extra));

        $html = $this->actingAs($admin)->withSession(['success' => 'Documento eliminado.'])->get($url('participants.show'))->assertOk()->getContent();
        $this->assertSame(1, substr_count($html, 'Documento eliminado.'), 'el mensaje de éxito debe mostrarse una sola vez');
        $this->assertStringContainsString('Notas de la Postulación', $html);

        // El botón de eliminar está junto a Revocar (mismo bloque del encabezado)
        $this->assertMatchesRegularExpression('/Revocar<\/button><\/form>\s*@?\s*.{0,900}Eliminar postulación/s', $html);

        // Guardar una nota desde el widget la muestra en el hub (y no en "Notas anteriores")
        $this->actingAs($admin)->post(route('admin.participants.notes.store', ['user' => $process->user_id]), ['application_id' => $process->application_id, 'content' => 'Nota del hub ABC'])->assertRedirect();
        $this->assertDatabaseHas('participant_notes', ['application_id' => $process->application_id, 'content' => 'Nota del hub ABC']);
        $html = $this->actingAs($admin)->get($url('participants.show'))->assertOk()->getContent();
        $this->assertSame(1, substr_count($html, 'Nota del hub ABC'));
        $this->assertStringNotContainsString('Notas anteriores', $html);
    }

    public function test_visa_tab_has_program_dates_that_save_on_the_process(): void
    {
        $admin = $this->admin();
        $process = $this->processFor($this->participant(), $this->program);
        $url = fn (string $name, array $extra = []) => route("admin.program.{$name}", array_merge(['program' => $this->program->slug, 'process' => $process->id], $extra));

        $this->actingAs($admin)->get($url('participants.show', ['tab' => 'visa']))->assertOk()->assertSee('Fechas del programa');
        $this->actingAs($admin)->put($url('visa.update'), ['program_start_date' => '2026-06-15', 'program_end_date' => '2026-09-15'])->assertSessionHasNoErrors();
        $this->assertSame('2026-06-15', $process->fresh()->program_start_date->toDateString());
        $this->assertSame('2026-09-15', $process->fresh()->program_end_date->toDateString());
        $this->actingAs($admin)->put($url('visa.update'), ['program_start_date' => '2026-06-15', 'program_end_date' => '2026-01-01'])->assertSessionHasErrors('program_end_date');
    }

    public function test_sponsor_with_placements_is_deactivated_not_deleted_and_stays_on_the_participant(): void
    {
        $admin = $this->admin();
        $process = $this->processFor($this->participant(), $this->program);
        $process->update(['current_stage_key' => 'placement']);
        $sponsor = Sponsor::create(['name' => 'Old Sponsor', 'code' => 'OLD', 'country' => 'USA', 'is_active' => true]);
        $fresh = Sponsor::create(['name' => 'New Sponsor', 'code' => 'NEW', 'country' => 'USA', 'is_active' => true]);
        JobPlacement::create(['program_process_id' => $process->id, 'sponsor_id' => $sponsor->id, 'status' => 'pending']);

        $this->actingAs($admin)->delete(route('admin.sponsors.destroy', $sponsor->id))->assertRedirect(route('admin.sponsors.index'))->assertSessionHas('success');
        $this->assertDatabaseHas('sponsors', ['id' => $sponsor->id, 'is_active' => 0]);
        $this->assertDatabaseHas('job_placements', ['program_process_id' => $process->id, 'sponsor_id' => $sponsor->id]);

        // Un sponsor sin historial sí se borra
        $this->actingAs($admin)->delete(route('admin.sponsors.destroy', $fresh->id))->assertRedirect();
        $this->assertDatabaseMissing('sponsors', ['id' => $fresh->id]);

        // En Job Placement el inactivo asignado sigue visible (marcado) y no aparecen inactivos ajenos
        $other = Sponsor::create(['name' => 'Retired Sponsor', 'code' => 'RET', 'country' => 'USA', 'is_active' => false]);
        $html = $this->actingAs($admin)->get(route('admin.program.participants.show', ['program' => $this->program->slug, 'process' => $process->id, 'tab' => 'placement']))->assertOk()->getContent();
        $this->assertStringContainsString('Old Sponsor (OLD) — inactivo', $html);
        $this->assertStringNotContainsString('Retired Sponsor', $html);

        // Menú lateral con acceso a Sponsors
        $this->assertStringContainsString(route('admin.sponsors.index'), $html);
    }
}
