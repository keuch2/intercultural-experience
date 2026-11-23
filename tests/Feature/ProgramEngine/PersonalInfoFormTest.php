<?php

namespace Tests\Feature\ProgramEngine;

use App\Models\Application;
use App\Models\AuPairProcess;
use App\Models\City;
use App\Models\EmergencyContact;
use App\Models\Notification;
use App\Models\Program;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\WorkExperience;
use App\Services\PersonalInfoService;
use Database\Seeders\WorkTravelProgramSeeder;
use Illuminate\Http\UploadedFile;

/**
 * Pedido de IE (nov 2026): formulario de información personal obligatorio en Admisión, precargado,
 * todo obligatorio salvo Laboral, confirmado una vez por postulación; sin confirmar no se suben documentos
 * ni se avanza de Admisión (solo con `personal_info_required` activo en Configuración).
 */
class PersonalInfoFormTest extends EngineTestCase
{
    private Program $program;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WorkTravelProgramSeeder::class);
        $this->program = Program::where('slug', 'work-travel')->firstOrFail();
        $this->user = User::factory()->create([
            'role' => 'user', 'name' => 'Ana Gomez', 'phone' => '0981 123 456', 'birth_date' => '2002-03-15',
            'country' => 'Paraguay', 'city' => 'Luque', 'academic_level' => 'licenciatura', 'english_level' => 'intermedio',
        ]);
    }

    private function requireConfirmation(bool $on = true): void
    {
        SystemSetting::set(PersonalInfoService::SETTING_KEY, $on, 'boolean', 'participants');
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ana Gomez', 'phone' => '0981 123 456', 'birth_date' => '2002-03-15', 'gender' => 'female',
            'nationality' => 'Paraguaya', 'ci_number' => '4.567.890', 'marital_status' => 'single',
            'country' => 'Paraguay', 'city_id' => City::where('name', 'Luque')->value('id'), 'address' => 'Av. Siempre Viva 123',
            'academic_level' => 'licenciatura', 'university' => 'UNA', 'career' => 'Administración', 'academic_year' => '3er año',
            'english_level' => 'intermedio', 'medical_conditions' => 'Ninguna', 'allergies' => 'Ninguna', 'medications' => 'Ninguno',
            'emergency_contacts' => [['name' => 'María Gomez', 'relationship' => 'Madre', 'phone' => '0982 555 111']],
            'work_experiences' => [],
        ], $overrides);
    }

    private function upload(): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->user, 'sanctum')->postJson('/api/programs/work-travel/documents',
            ['document_type' => 'cedula', 'files' => [UploadedFile::fake()->create('c.pdf', 10)]]);
    }

    public function test_form_is_prefilled_with_existing_data(): void
    {
        EmergencyContact::create(['user_id' => $this->user->id, 'name' => 'Pedro', 'relationship' => 'Padre', 'phone' => '0981 000 000', 'is_primary' => true]);
        $application = $this->applicationFor($this->user, $this->program, 'pending');

        $response = $this->actingAs($this->user, 'sanctum')->getJson("/api/me/personal-info?application_id={$application->id}")->assertOk();

        $response->assertJsonPath('data.phone', '0981 123 456')
            ->assertJsonPath('data.birth_date', '2002-03-15')
            ->assertJsonPath('data.city', 'Luque')
            ->assertJsonPath('data.emergency_contacts.0.name', 'Pedro')
            ->assertJsonPath('personal_info.confirmed', false)
            ->assertJsonPath('personal_info.complete', false);
        $this->assertContains('health', $response->json('personal_info.missing_sections'));
        $this->assertNotContains('emergency', $response->json('personal_info.missing_sections'));
        $this->assertCount(3, $response->json('options.gender'));
    }

    public function test_every_section_is_required_except_work(): void
    {
        $this->actingAs($this->user, 'sanctum')->putJson('/api/me/personal-info', ['work_experiences' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['gender', 'nationality', 'ci_number', 'marital_status', 'address', 'university', 'career',
                'academic_year', 'medical_conditions', 'allergies', 'medications', 'emergency_contacts'])
            ->assertJsonMissingValidationErrors(['work_experiences']);

        $this->actingAs($this->user, 'sanctum')->putJson('/api/me/personal-info', $this->payload(['work_experiences' => [['company' => 'ACME']]]))
            ->assertStatus(422)->assertJsonValidationErrors(['work_experiences.0.position', 'work_experiences.0.start_date']);
    }

    public function test_saving_and_confirming_for_an_application(): void
    {
        $application = $this->applicationFor($this->user, $this->program, 'pending');
        $old = EmergencyContact::create(['user_id' => $this->user->id, 'name' => 'Viejo', 'relationship' => 'Tío', 'phone' => '0981 000 000']);

        $this->actingAs($this->user, 'sanctum')->putJson('/api/me/personal-info', $this->payload([
            'application_id' => $application->id,
            'work_experiences' => [['company' => 'ACME', 'position' => 'Cajera', 'start_date' => '2024-01-01', 'is_current' => true, 'end_date' => '2025-01-01']],
        ]))->assertOk()->assertJsonPath('personal_info.confirmed', true)->assertJsonPath('personal_info.complete', true);

        $user = $this->user->fresh();
        $this->assertSame('female', $user->gender);
        $this->assertSame('Luque', $user->city);
        $this->assertSame('Ninguna', $user->allergies);
        $this->assertNotNull($application->fresh()->personal_info_confirmed_at);
        $this->assertNull(EmergencyContact::find($old->id));
        $this->assertTrue(EmergencyContact::where('user_id', $user->id)->sole()->is_primary);
        $work = WorkExperience::where('user_id', $user->id)->sole();
        $this->assertTrue($work->is_current);
        $this->assertNull($work->end_date);

        // Otra persona no puede confirmar una postulación ajena.
        $other = $this->participant();
        $this->actingAs($other, 'sanctum')->putJson('/api/me/personal-info', $this->payload(['application_id' => $application->id]))->assertNotFound();
    }

    public function test_nothing_is_blocked_while_the_setting_is_off(): void
    {
        $this->processFor($this->user, $this->program);

        $this->actingAs($this->user, 'sanctum')->getJson('/api/programs/work-travel/process')
            ->assertJsonPath('data.personal_info.required', false)
            ->assertJsonPath('data.personal_info.pending', false);
        $this->upload()->assertStatus(201);
    }

    public function test_required_form_blocks_uploads_and_is_the_next_action(): void
    {
        $this->requireConfirmation();
        $process = $this->processFor($this->user, $this->program);

        $this->actingAs($this->user, 'sanctum')->getJson('/api/programs/work-travel/process')
            ->assertJsonPath('data.personal_info.pending', true)
            ->assertJsonPath('data.next_action.key', 'personal_info')
            ->assertJsonPath('data.next_action.screen', 'PersonalInfo')
            ->assertJsonPath('data.next_action.params.application_id', $process->application_id);
        $this->upload()->assertStatus(403)->assertJsonPath('code', 'personal_info_pending');

        $this->actingAs($this->user, 'sanctum')->putJson('/api/me/personal-info', $this->payload(['application_id' => $process->application_id]))->assertOk();

        $this->actingAs($this->user, 'sanctum')->getJson('/api/programs/work-travel/process')
            ->assertJsonPath('data.personal_info.pending', false)
            ->assertJsonPath('data.next_action.key', 'upload_docs');
        $this->upload()->assertStatus(201);

        // Un programa nuevo vuelve a pedir la confirmación.
        $this->assertNull(Application::create(['user_id' => $this->user->id, 'program_id' => $this->program->id, 'status' => 'pending'])->personal_info_confirmed_at);
    }

    public function test_admission_guard_and_admin_confirmation(): void
    {
        $this->requireConfirmation();
        $process = $this->processFor($this->user, $this->program);
        $admin = $this->admin();
        $url = route('admin.program.participants.show', [$this->program->slug, $process->id]);

        $reasons = app(\App\Services\ProgramEngine\StageEvaluator::class)->blockingReasons($process->fresh());
        $this->assertContains('El participante no confirmó su información personal.', $reasons);

        $this->actingAs($admin)->get($url)->assertOk()->assertSee('Información personal sin confirmar');

        // Con datos incompletos IE no puede marcarla confirmada.
        $this->actingAs($admin)->post(route('admin.applications.personal-info.confirm', $process->application))->assertSessionHas('error');

        $this->actingAs($this->user, 'sanctum')->putJson('/api/me/personal-info', $this->payload())->assertOk();
        $this->actingAs($admin)->post(route('admin.applications.personal-info.confirm', $process->application))->assertSessionHas('success');

        $application = $process->application->fresh();
        $this->assertSame($admin->id, $application->personal_info_confirmed_by);
        $this->assertNotContains('El participante no confirmó su información personal.',
            app(\App\Services\ProgramEngine\StageEvaluator::class)->blockingReasons($process->fresh()));
        $this->actingAs($admin)->get($url)->assertOk()->assertSee('Información personal confirmada')->assertSee('marcada por IE');
    }

    public function test_au_pair_uploads_and_advance_require_confirmation(): void
    {
        $this->requireConfirmation();
        $auPair = Program::create(['name' => 'Au Pair USA', 'description' => 'x', 'country' => 'USA', 'main_category' => 'IE',
            'subcategory' => 'Au Pair', 'is_active' => true, 'is_available_in_app' => true]);
        $application = $this->applicationFor($this->user, $auPair, 'approved');
        $process = AuPairProcess::create(AuPairProcess::initialAttributes($application));

        $this->actingAs($this->user, 'sanctum')->getJson('/api/au-pair/process')
            ->assertJsonPath('data.personal_info.pending', true)->assertJsonPath('data.next_action.key', 'personal_info');
        $this->actingAs($this->user, 'sanctum')->postJson('/api/au-pair/documents', ['document_type' => 'cedula', 'stage' => 'admission',
            'files' => [UploadedFile::fake()->create('c.pdf', 10, 'application/pdf')]])
            ->assertStatus(403)->assertJsonPath('status', 'personal_info_pending');
        $this->assertFalse($process->fresh()->canAdvanceToApplication());
        $this->actingAs($this->admin())->get(route('admin.aupair.profiles.show', ['id' => $this->user->id, 'tab' => 'admission']))
            ->assertOk()->assertSee('Información personal sin confirmar');

        $this->actingAs($this->user, 'sanctum')->putJson('/api/me/personal-info', $this->payload(['application_id' => $application->id]))->assertOk();
        $this->actingAs($this->user, 'sanctum')->getJson('/api/au-pair/process')
            ->assertJsonPath('data.personal_info.confirmed', true)->assertJsonPath('data.next_action.key', 'upload_admission_docs');
    }

    public function test_settings_toggle_and_weekly_reminder(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->get(route('admin.settings.index'))->assertOk()->assertSee('Información personal en Admisión');
        $this->actingAs($admin)->post(route('admin.settings.personal-info.update'), ['personal_info_required' => '1'])->assertSessionHas('success');
        $this->assertTrue(PersonalInfoService::isRequired());

        $this->processFor($this->user, $this->program);
        $this->artisan('participants:send-reminders', ['--date' => now()->toDateString()])->assertSuccessful();
        $this->artisan('participants:send-reminders', ['--date' => now()->addDay()->toDateString()])->assertSuccessful();
        $this->assertSame(1, Notification::where('user_id', $this->user->id)->where('title', 'Completá tu información personal')->count());

        $this->actingAs($admin)->post(route('admin.settings.personal-info.update'), ['personal_info_required' => '0']);
        $this->assertFalse(PersonalInfoService::isRequired());
    }
}
