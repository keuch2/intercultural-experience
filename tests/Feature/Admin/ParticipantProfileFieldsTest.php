<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Reporte del cliente (nov 2026): datos cargados al crear el participante que no aparecían en el
 * Perfil General (género, información académica), edad, y campos de salud/emergencia quitados.
 */
class ParticipantProfileFieldsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_general_profile_shows_gender_age_id_and_academic_info(): void
    {
        $participant = User::factory()->create([
            'role' => 'user', 'gender' => 'female', 'birth_date' => now()->subYears(23)->subDay()->toDateString(),
            'ci_number' => '4567890', 'academic_level' => 'licenciatura', 'university' => 'UNA',
            'career' => 'Ingeniería', 'academic_year' => '2024', 'english_level' => 'avanzado',
            'blood_type' => 'O+', 'health_insurance' => 'IPS',
        ]);

        $this->actingAs($this->admin())->get(route('admin.participants.show', $participant))
            ->assertOk()
            ->assertSee('Femenino')->assertSee('(23 años)')->assertSee('4567890')
            ->assertSee('Información Académica')->assertSee('Licenciatura')->assertSee('UNA')
            ->assertSee('Ingeniería')->assertSee('Avanzado')
            ->assertDontSee('Tipo de Sangre')->assertDontSee('Seguro Médico')->assertDontSee('Contacto Médico de Emergencia');
    }

    public function test_admin_can_edit_gender_and_academic_info(): void
    {
        $participant = User::factory()->create(['role' => 'user']);
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.participants.edit', $participant))
            ->assertOk()->assertSee('name="gender"', false)->assertSee('name="university"', false);

        $this->actingAs($admin)->put(route('admin.participants.update', $participant), [
            'name' => 'Ana Gomez', 'gender' => 'female', 'academic_level' => 'maestria',
            'university' => 'UCA', 'career' => 'Derecho', 'academic_year' => '2025', 'english_level' => 'intermedio',
            'blood_type' => 'A+',
        ])->assertSessionHasNoErrors();

        $participant->refresh();
        $this->assertSame('female', $participant->gender);
        $this->assertSame(['maestria', 'UCA', 'Derecho', '2025', 'intermedio'], [
            $participant->academic_level, $participant->university, $participant->career, $participant->academic_year, $participant->english_level,
        ]);
        $this->assertNull($participant->blood_type, 'Tipo de sangre ya no se edita desde el perfil.');
    }

    public function test_creating_a_participant_keeps_emergency_contact_out_of_medical_fields(): void
    {
        $this->actingAs($this->admin())->post(route('admin.participants.store'), [
            'name' => 'Luis Paz', 'email' => 'luis.paz@example.com', 'gender' => 'male',
            'academic_level' => 'bachiller', 'institution' => 'Colegio X', 'field_of_study' => 'Ciencias', 'graduation_year' => 2022,
            'medications' => 'Ninguno', 'emergency_contact_name' => 'María Paz', 'emergency_contact_relationship' => 'Madre',
            'emergency_contact_phone' => '0981000000',
        ])->assertSessionHasNoErrors();

        $user = User::where('email', 'luis.paz@example.com')->firstOrFail();
        $this->assertSame(['male', 'Colegio X', 'Ciencias', '2022', 'Ninguno'], [$user->gender, $user->university, $user->career, (string) $user->academic_year, $user->medications]);
        $this->assertNull($user->emergency_medical_contact);
        $this->assertSame('María Paz', $user->emergencyContacts()->value('name'));
    }
}
