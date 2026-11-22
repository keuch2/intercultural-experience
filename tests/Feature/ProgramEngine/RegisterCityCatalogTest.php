<?php

namespace Tests\Feature\ProgramEngine;

use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pedido de IE (nov 2026): registro con todos los campos obligatorios, fecha de nacimiento, teléfono
 * y ciudad elegida de un catálogo de Paraguay (con "Otra").
 */
class RegisterCityCatalogTest extends TestCase
{
    use RefreshDatabase;

    private function strictPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ana Gomez', 'email' => 'ana.ciudad@example.com',
            'password' => 'Clave-Segura-2026!', 'password_confirmation' => 'Clave-Segura-2026!',
            'phone' => '0981 123 456', 'birth_date' => '2002-03-15', 'country' => 'Paraguay',
            'city_id' => City::where('name', 'Luque')->value('id'),
            'academic_level' => 'licenciatura', 'english_level' => 'intermedio',
            'accept_terms' => true, 'registration_version' => 2,
        ], $overrides);
    }

    public function test_catalog_is_public_and_grouped_by_department(): void
    {
        $response = $this->getJson('/api/cities')->assertOk();

        $this->assertCount(113, $response->json('data'));
        $this->assertSame('Asunción', $response->json('data.0.label'));
        $this->assertContains('Luque — Central', collect($response->json('data'))->pluck('label'));
    }

    public function test_strict_registration_requires_every_field(): void
    {
        $this->postJson('/api/register', ['registration_version' => 2, 'name' => 'Ana Gomez', 'email' => 'x@example.com',
            'password' => 'Clave-Segura-2026!', 'password_confirmation' => 'Clave-Segura-2026!'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['phone', 'birth_date', 'country', 'city', 'academic_level', 'english_level', 'accept_terms']);
    }

    public function test_strict_registration_saves_catalog_city_phone_and_birth_date(): void
    {
        $this->postJson('/api/register', $this->strictPayload())->assertCreated();

        $user = User::where('email', 'ana.ciudad@example.com')->firstOrFail();
        $this->assertSame('Luque', $user->city);
        $this->assertSame('Central', $user->cityRef->department);
        $this->assertSame('0981 123 456', $user->phone);
        $this->assertSame('2002-03-15', $user->birth_date->toDateString());
    }

    public function test_other_city_is_free_text_without_catalog_link(): void
    {
        $this->postJson('/api/register', $this->strictPayload(['city_id' => null, 'city' => 'Colonia Fram Norte']))->assertCreated();

        $user = User::where('email', 'ana.ciudad@example.com')->firstOrFail();
        $this->assertSame('Colonia Fram Norte', $user->city);
        $this->assertNull($user->city_id);
    }

    public function test_old_app_versions_can_still_register_and_city_text_is_linked(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Ana Gomez', 'email' => 'ana.vieja@example.com',
            'password' => 'Clave-Segura-2026!', 'password_confirmation' => 'Clave-Segura-2026!', 'city' => 'asuncion',
        ])->assertCreated();

        $this->assertSame('Asunción', User::where('email', 'ana.vieja@example.com')->firstOrFail()->cityRef?->name);
    }

    public function test_city_text_matching_handles_accents_aliases_and_country(): void
    {
        $this->assertSame('San Estanislao (Santaní)', City::matchName('SANTANI')?->name);
        $this->assertSame('Ciudad del Este', City::matchName('CDE')?->name);
        $this->assertSame('Ñemby', City::matchName('nemby')?->name);
        $this->assertSame('San Lorenzo', City::matchName('San Lorenzo, Paraguay')?->name);
        $this->assertNull(City::matchName('Buenos Aires'));

        $user = User::factory()->create(['city' => 'Encarnacion', 'country' => 'Paraguay']);
        $this->assertSame('Encarnación', $user->cityRef?->name);

        $user->update(['country' => 'Argentina', 'city' => 'Posadas']);
        $this->assertNull($user->fresh()->city_id);
    }

    public function test_profile_update_accepts_catalog_city_and_other(): void
    {
        $user = User::factory()->create(['role' => 'user', 'country' => 'Paraguay']);
        $villarrica = City::where('name', 'Villarrica')->value('id');

        $this->actingAs($user, 'sanctum')->putJson('/api/profile', ['city_id' => $villarrica, 'city' => 'x'])
            ->assertOk()->assertJsonPath('user.city', 'Villarrica')->assertJsonPath('user.city_id', $villarrica);

        $this->actingAs($user, 'sanctum')->putJson('/api/profile', ['city' => 'Compañía Ybyraty'])
            ->assertOk()->assertJsonPath('user.city_id', null);
    }
}
