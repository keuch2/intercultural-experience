<?php

namespace Tests\Feature\ProgramEngine;

use App\Models\Notification;
use App\Models\Program;
use App\Services\ProgramEngine\JobPoolService;
use Database\Seeders\WorkTravelProgramSeeder;

class JobPoolNotificationTest extends EngineTestCase
{
    public function test_notifications_for_publish_select_release_and_exhaustion(): void
    {
        $this->seed(WorkTravelProgramSeeder::class);
        $program = Program::where('slug', 'work-travel')->firstOrFail();
        $pool = app(JobPoolService::class);
        $admin = $this->admin();

        $enabled = $this->processFor($this->participant(), $program);
        $enabled->update(['module_access' => ['job_pool' => ['enabled' => true]]]);
        $notEnabled = $this->processFor($this->participant(), $program);

        $offer = $pool->publish($program, ['employer_name' => 'Six Flags', 'state' => 'Texas', 'city' => 'Arlington', 'positions_total' => 1], null, $admin);

        $this->assertDatabaseHas('notifications', ['user_id' => $enabled->user_id, 'category' => 'job_pool', 'title' => 'Nueva oferta laboral disponible']);
        $this->assertDatabaseMissing('notifications', ['user_id' => $notEnabled->user_id, 'category' => 'job_pool']);

        $assignment = $pool->select($offer, $enabled->fresh());
        $this->assertDatabaseHas('notifications', ['user_id' => $enabled->user_id, 'title' => 'Oferta laboral seleccionada']);
        $this->assertDatabaseHas('notifications', ['user_id' => $admin->id, 'title' => 'Selección de oferta laboral']);
        $this->assertDatabaseHas('notifications', ['user_id' => $admin->id, 'title' => 'Oferta sin posiciones disponibles']);

        $pool->release($assignment, $admin, 'Cambio');
        $this->assertDatabaseHas('notifications', ['user_id' => $enabled->user_id, 'title' => 'Asignación de oferta liberada']);
        $this->assertGreaterThanOrEqual(3, Notification::where('user_id', $enabled->user_id)->count());
    }
}
