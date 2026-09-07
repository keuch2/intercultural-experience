<?php

namespace Tests\Feature\ProgramEngine;

use App\Models\Program;

class ProgramConfigAdminTest extends EngineTestCase
{
    public function test_admin_can_open_config_and_create_stage_document_gate_checklist(): void
    {
        $admin = $this->admin();
        $program = Program::create([
            'name' => 'Nuevo Programa', 'slug' => 'nuevo-programa', 'description' => 'x', 'country' => 'USA',
            'main_category' => 'IE', 'subcategory' => 'Nuevo', 'is_active' => true,
        ]);

        $this->actingAs($admin)->get(route('admin.program-config.show', $program))->assertOk()->assertSee('Motor de programa');

        $this->actingAs($admin)->post(route('admin.program-config.stages.store', $program), [
            'key' => 'admission', 'label' => 'Admisión', 'guards' => ['require_docs_approved' => 1],
        ])->assertRedirect();
        $this->actingAs($admin)->post(route('admin.program-config.stages.store', $program), [
            'key' => 'completed', 'label' => 'Completado', 'is_terminal' => 1, 'guards' => ['manual_only' => 1],
        ])->assertRedirect();
        $this->assertDatabaseHas('program_stages', ['program_id' => $program->id, 'key' => 'admission']);
        $this->assertDatabaseHas('program_stages', ['program_id' => $program->id, 'key' => 'completed', 'is_terminal' => 1]);

        $this->actingAs($admin)->post(route('admin.program-config.gates.store', $program), [
            'key' => 'inscription', 'label' => 'Pago de inscripción', 'amount' => 250,
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('admin.program-config.documents.store', $program), [
            'key' => 'passport', 'label' => 'Pasaporte', 'stage_key' => 'admission', 'uploaded_by' => 'participant',
            'is_required' => 1, 'min_count' => 1, 'unlock_gate_key' => 'inscription',
        ])->assertRedirect();
        $this->assertDatabaseHas('program_document_requirements', ['program_id' => $program->id, 'key' => 'passport', 'unlock_gate_key' => 'inscription']);

        $this->actingAs($admin)->post(route('admin.program-config.checklist.store', $program), [
            'key' => 'welcome_email_sent', 'label' => 'Correo de bienvenida', 'item_type' => 'boolean', 'stage_key' => 'admission',
        ])->assertRedirect();

        $this->actingAs($admin)->put(route('admin.program-config.modules.update', $program), [
            'modules' => ['english_test', 'visa', 'invalid_module'], 'engine_enabled' => 1, 'is_available_in_app' => 1,
        ])->assertRedirect();
        $program->refresh();
        $this->assertSame(['english_test', 'visa'], $program->modules);
        $this->assertTrue($program->engine_enabled);
        $this->assertTrue($program->is_available_in_app);

        $this->actingAs($admin)->put(route('admin.program-config.rules.update', $program), [
            'min_english_level' => 'B2', 'max_english_attempts' => 2, 'support_log_types' => 'incident, final_evaluation',
            'visa_sections' => ['c1', 'c4'],
        ])->assertRedirect();
        $program->refresh();
        $this->assertSame('B2', $program->rule('min_english_level'));
        $this->assertSame(['incident', 'final_evaluation'], $program->rule('support_log_types'));
    }

    public function test_validation_rejects_duplicate_or_invalid_keys(): void
    {
        $admin = $this->admin();
        $program = $this->engineProgram();

        $this->actingAs($admin)->from(route('admin.program-config.show', $program))
            ->post(route('admin.program-config.stages.store', $program), ['key' => 'admission', 'label' => 'Dup'])
            ->assertSessionHasErrors('key');

        $this->actingAs($admin)->from(route('admin.program-config.show', $program))
            ->post(route('admin.program-config.stages.store', $program), ['key' => 'Bad Key', 'label' => 'X'])
            ->assertSessionHasErrors('key');

        $this->actingAs($admin)->from(route('admin.program-config.show', $program))
            ->post(route('admin.program-config.documents.store', $program), ['key' => 'x_doc', 'label' => 'X', 'stage_key' => 'nope', 'uploaded_by' => 'participant'])
            ->assertSessionHasErrors('stage_key');
    }

    public function test_cannot_delete_requirement_with_uploads_or_stage_with_participants(): void
    {
        $admin = $this->admin();
        $program = $this->engineProgram();
        $process = $this->processFor($this->participant(), $program);
        $this->approvedDoc($process, 'cedula', 'admission');

        $req = $program->documentRequirements()->where('key', 'cedula')->first();
        $this->actingAs($admin)->delete(route('admin.program-config.documents.destroy', [$program, $req]))->assertSessionHas('error');
        $this->assertDatabaseHas('program_document_requirements', ['id' => $req->id]);

        $stage = $program->stages()->where('key', 'admission')->first();
        $this->actingAs($admin)->delete(route('admin.program-config.stages.destroy', [$program, $stage]))->assertSessionHas('error');
        $this->assertDatabaseHas('program_stages', ['id' => $stage->id]);
    }

    public function test_every_config_tab_renders(): void
    {
        $admin = $this->admin();
        $program = $this->engineProgram();
        $program->resources()->create(['title' => 'Guía', 'file_type' => 'PDF', 'is_active' => true]);

        foreach (array_keys(\App\Http\Controllers\Admin\ProgramConfigController::TABS) as $tab) {
            $this->actingAs($admin)
                ->get(route('admin.program-config.show', ['program' => $program->id, 'tab' => $tab]))
                ->assertOk();
        }
    }

    public function test_non_admin_cannot_access_config(): void
    {
        $program = $this->engineProgram();
        $this->actingAs($this->participant())->get(route('admin.program-config.show', $program))->assertRedirect(route('login'));
    }
}
