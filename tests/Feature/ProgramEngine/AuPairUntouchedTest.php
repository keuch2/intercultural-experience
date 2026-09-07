<?php

namespace Tests\Feature\ProgramEngine;

use App\Models\Application;
use App\Models\Program;
use Laravel\Sanctum\Sanctum;

/**
 * Regresión: el motor no debe alterar el comportamiento de Au Pair.
 */
class AuPairUntouchedTest extends EngineTestCase
{
    public function test_au_pair_program_is_still_available_in_app_without_column_flag(): void
    {
        $program = Program::create([
            'name' => 'Au Pair USA', 'description' => 'x', 'country' => 'USA', 'main_category' => 'IE',
            'subcategory' => Program::SUBCATEGORY_AU_PAIR, 'is_active' => true,
        ]);

        $program->refresh();
        $this->assertFalse((bool) $program->getAttributes()['is_available_in_app']);
        $this->assertTrue($program->is_available_in_app);
        $this->assertFalse($program->engine_enabled);
        $this->assertNull($program->slug);
    }

    public function test_au_pair_process_endpoint_still_works(): void
    {
        $program = Program::create([
            'name' => 'Au Pair USA', 'description' => 'x', 'country' => 'USA', 'main_category' => 'IE',
            'subcategory' => Program::SUBCATEGORY_AU_PAIR, 'is_active' => true,
        ]);
        $user = $this->participant();
        Application::create(['user_id' => $user->id, 'program_id' => $program->id, 'status' => 'approved', 'applied_at' => now()]);

        Sanctum::actingAs($user);
        $this->getJson('/api/au-pair/process')
            ->assertOk()
            ->assertJsonPath('data.current_stage', 'admission')
            ->assertJsonPath('data.application_approved', true);

        $this->assertDatabaseCount('program_processes', 0);
    }

    public function test_engine_program_does_not_leak_into_au_pair_resolution(): void
    {
        $auPair = Program::create([
            'name' => 'Au Pair USA', 'description' => 'x', 'country' => 'USA', 'main_category' => 'IE',
            'subcategory' => Program::SUBCATEGORY_AU_PAIR, 'is_active' => true,
        ]);
        $engine = $this->engineProgram();
        $user = $this->participant();
        Application::create(['user_id' => $user->id, 'program_id' => $engine->id, 'status' => 'approved', 'applied_at' => now()]);

        Sanctum::actingAs($user);
        $this->getJson('/api/au-pair/process')->assertStatus(404)->assertJsonPath('status', 'no_application');
        $this->assertTrue($auPair->is_available_in_app);
    }
}
