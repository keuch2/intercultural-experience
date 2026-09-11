<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: /admin/programs/{id}/forms fallaba con "Route [admin.programs.index] not defined"
 * porque las vistas legacy referencian rutas renombradas a ie-programs.
 */
class ProgramFormsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_program_forms_index_renders_and_legacy_aliases_redirect(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $program = Program::create(['name' => 'Demo', 'slug' => 'demo', 'description' => 'x', 'country' => 'USA', 'main_category' => 'IE', 'subcategory' => 'Demo', 'is_active' => true]);

        $this->actingAs($admin)->get(route('admin.programs.forms.index', $program))->assertOk();
        $this->actingAs($admin)->get(route('admin.programs.index'))->assertRedirect(route('admin.ie-programs.index'));
        $this->actingAs($admin)->get(route('admin.programs.show', $program))->assertRedirect(route('admin.ie-programs.show', $program));
    }
}
