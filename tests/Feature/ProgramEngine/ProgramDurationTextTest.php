<?php

namespace Tests\Feature\ProgramEngine;

use App\Models\Program;

class ProgramDurationTextTest extends EngineTestCase
{
    public function test_admin_saves_free_text_duration_and_api_returns_it_as_is(): void
    {
        $program = Program::create([
            'name' => 'Language Program', 'description' => 'Test', 'country' => 'USA', 'main_category' => 'IE',
            'subcategory' => 'Language Exchange', 'is_active' => true, 'is_available_in_app' => true,
        ]);

        $this->actingAs($this->admin())->get(route('admin.ie-programs.edit', $program))
            ->assertOk()->assertSee('name="duration"', false)->assertDontSee('duration_unit');

        $this->actingAs($this->admin())->put(route('admin.ie-programs.update', $program), [
            'name' => 'Language Program', 'description' => 'Test', 'country' => 'USA',
            'subcategory' => 'Language Exchange', 'is_active' => 1, 'duration' => '6 meses a 1 año',
        ])->assertSessionHasNoErrors();

        $this->assertSame('6 meses a 1 año', $program->fresh()->duration);
        $this->getJson("/api/public/programs/{$program->id}")->assertOk()->assertJsonPath('data.duration', '6 meses a 1 año');
    }
}
