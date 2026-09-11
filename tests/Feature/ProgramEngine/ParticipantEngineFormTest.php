<?php

namespace Tests\Feature\ProgramEngine;

use App\Models\Program;
use App\Models\ProgramProcess;

/**
 * Al editar un participante y elegir un programa del motor (Work & Travel), el admin
 * ya no debe ver el formulario legacy work_travel_data sino un aviso con enlace al hub,
 * y la postulación creada desde el admin debe tener su ProgramProcess.
 */
class ParticipantEngineFormTest extends EngineTestCase
{
    public function test_program_form_endpoint_returns_engine_notice_instead_of_legacy_form(): void
    {
        $program = $this->engineProgram(['name' => 'Work & Travel USA', 'slug' => 'work-travel']);
        $user = $this->participant();

        $res = $this->actingAs($this->admin())
            ->get(route('admin.participants.program-form', ['participant' => $user->id, 'formType' => 'engine']).'?program_id='.$program->id);

        $res->assertOk()
            ->assertSee('se gestiona con el flujo de trabajo del programa')
            ->assertSee(route('admin.program.participants.index', ['program' => $program->slug, 'search' => $user->email]), false)
            ->assertDontSee('Datos Específicos - Work & Travel USA');

        // El tipo legacy 'work_travel' tampoco muestra el formulario viejo.
        $this->actingAs($this->admin())
            ->get(route('admin.participants.program-form', ['participant' => $user->id, 'formType' => 'work_travel']))
            ->assertOk()
            ->assertDontSee('Datos Específicos - Work & Travel USA');
    }

    public function test_edit_view_marks_engine_programs_and_drops_work_travel_legacy_map(): void
    {
        $program = $this->engineProgram(['slug' => 'work-travel']);
        $user = $this->participant();

        $res = $this->actingAs($this->admin())->get(route('admin.participants.edit', $user->id));

        $res->assertOk()
            ->assertSee('value="'.$program->id.'"', false)
            ->assertSee('data-engine="1"', false)
            ->assertDontSee("'Work and Travel': 'work_travel'", false);
    }

    public function test_assigning_engine_program_from_admin_creates_process(): void
    {
        $program = $this->engineProgram(['slug' => 'work-travel']);
        $user = $this->participant();

        $this->actingAs($this->admin())
            ->put(route('admin.participants.update', $user->id), [
                'name' => $user->name,
                'email' => $user->email,
                'program_id' => $program->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('applications', ['user_id' => $user->id, 'program_id' => $program->id]);
        $this->assertSame(1, ProgramProcess::where('user_id', $user->id)->where('program_id', $program->id)->count());
    }

    public function test_assigning_legacy_program_does_not_create_process(): void
    {
        $plain = Program::create(['name' => 'Plain', 'slug' => 'plain', 'description' => 'x', 'country' => 'USA', 'main_category' => 'IE', 'subcategory' => 'Plain', 'is_active' => true]);
        $user = $this->participant();

        $this->actingAs($this->admin())
            ->put(route('admin.participants.update', $user->id), ['name' => $user->name, 'email' => $user->email, 'program_id' => $plain->id])
            ->assertRedirect();

        $this->assertSame(0, ProgramProcess::where('user_id', $user->id)->count());
    }

    public function test_new_application_modal_creates_application_and_process_without_touching_user(): void
    {
        $program = $this->engineProgram(['slug' => 'work-travel']);
        $user = $this->participant();

        $this->actingAs($this->admin())
            ->post(route('admin.participants.applications.store', $user->id), ['program_id' => $program->id, 'set_as_current' => 1])
            ->assertRedirect(route('admin.participants.show', $user->id))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('applications', ['user_id' => $user->id, 'program_id' => $program->id, 'status' => 'pending']);
        $this->assertSame(1, ProgramProcess::where('user_id', $user->id)->where('program_id', $program->id)->count());

        // Duplicado activo → error de validación, sin crear otra postulación
        $this->actingAs($this->admin())
            ->from(route('admin.participants.edit', $user->id))
            ->post(route('admin.participants.applications.store', $user->id), ['program_id' => $program->id])
            ->assertRedirect(route('admin.participants.edit', $user->id))
            ->assertSessionHasErrors('program_id');
        $this->assertSame(1, \App\Models\Application::where('user_id', $user->id)->count());

        // La ficha lista la postulación con enlace al hub del motor
        $process = ProgramProcess::where('user_id', $user->id)->first();
        $this->actingAs($this->admin())->get(route('admin.participants.show', $user->id))
            ->assertOk()
            ->assertSee(route('admin.program.participants.show', ['program' => $program->slug, 'process' => $process->id]), false);
    }
}
