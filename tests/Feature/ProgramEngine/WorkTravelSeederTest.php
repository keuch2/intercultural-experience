<?php

namespace Tests\Feature\ProgramEngine;

use App\Models\Program;
use App\Services\ProgramEngine\ProgramDefinition;
use Database\Seeders\WorkTravelProgramSeeder;

class WorkTravelSeederTest extends EngineTestCase
{
    public function test_seeder_is_idempotent_and_matches_spec(): void
    {
        $this->seed(WorkTravelProgramSeeder::class);
        $this->seed(WorkTravelProgramSeeder::class);

        $program = Program::where('slug', 'work-travel')->firstOrFail();
        $definition = ProgramDefinition::for($program);

        $this->assertTrue($program->engine_enabled);
        $this->assertTrue($program->is_available_in_app);
        $this->assertSame(['admission', 'application', 'job_pool', 'placement', 'visa', 'support', 'completed'], $definition->stages()->pluck('key')->all());
        $this->assertTrue($definition->stage('completed')->is_terminal);
        $this->assertSame(['inscription', 'program'], $definition->gates()->pluck('key')->all());
        $this->assertSame(['welcome_email_sent', 'contract_signed', 'sponsor_profile_created'], $definition->checklist()->pluck('key')->all());

        // Admisión = Au Pair sin foto de perfil
        $this->assertSame(['cedula', 'passport', 'drivers_license', 'enrollment_form'], $definition->requirements('admission')->pluck('key')->all());
        $this->assertNull($definition->requirement('profile_photo'));

        // Documentos de aplicación habilitados por el pago de inscripción
        $application = $definition->requirements('application');
        $this->assertCount(5, $application);
        $this->assertTrue($application->every(fn ($r) => $r->unlock_gate_key === 'inscription'));
        $this->assertFalse($definition->requirement('previous_visa')->is_required);

        // Placement: docs que carga IE
        $this->assertTrue($definition->requirements('placement')->every(fn ($r) => $r->uploaded_by === 'staff'));

        $this->assertCount(8, $program->resources);
        $this->assertDatabaseHas('sponsors', ['code' => 'AAG']);
        $this->assertSame(1, Program::where('slug', 'work-travel')->count());
    }

    public function test_seeder_adopts_legacy_work_travel_program_instead_of_duplicating(): void
    {
        $legacy = Program::create(['name' => 'W&T USA', 'description' => 'x', 'country' => 'USA', 'main_category' => 'IE', 'subcategory' => 'Work & Travel', 'is_active' => true]);

        $this->seed(WorkTravelProgramSeeder::class);

        $this->assertSame('work-travel', $legacy->fresh()->slug);
        $this->assertSame(1, Program::whereIn('subcategory', ['Work and Travel', 'Work & Travel'])->count());
    }
}
