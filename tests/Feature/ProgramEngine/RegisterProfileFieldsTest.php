<?php

namespace Tests\Feature\ProgramEngine;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterProfileFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_saves_optional_profile_fields(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Ana Gomez',
            'email' => 'ana.registro@example.com',
            'password' => 'Clave-Segura-2026!',
            'password_confirmation' => 'Clave-Segura-2026!',
            'city' => 'Asunción',
            'country' => 'Paraguay',
            'academic_level' => 'licenciatura',
            'english_level' => 'intermedio',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.city', 'Asunción')
            ->assertJsonPath('user.country', 'Paraguay')
            ->assertJsonPath('user.academic_level', 'licenciatura')
            ->assertJsonPath('user.english_level', 'intermedio');

        $user = User::where('email', 'ana.registro@example.com')->firstOrFail();
        $this->assertSame('Asunción', $user->city);
        $this->assertSame('intermedio', $user->english_level);
    }

    public function test_registration_rejects_unknown_levels(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Ana Gomez',
            'email' => 'ana.invalida@example.com',
            'password' => 'Clave-Segura-2026!',
            'password_confirmation' => 'Clave-Segura-2026!',
            'english_level' => 'experto',
        ])->assertStatus(422)->assertJsonValidationErrors('english_level');
    }
}
