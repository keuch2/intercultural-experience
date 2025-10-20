<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ResetPasswordNotification;
use Tests\TestCase;
use Carbon\Carbon;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Usuario puede solicitar reset de contraseña
     */
    public function test_user_can_request_password_reset()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        $response = $this->postJson('/api/password/forgot', [
            'email' => 'test@example.com'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success'
                 ]);

        // Verificar que se creó el token en la BD
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'test@example.com'
        ]);
    }

    /**
     * Test: Usuario recibe email con link de reset
     */
    public function test_user_receives_email_with_reset_link()
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        $this->postJson('/api/password/forgot', [
            'email' => 'test@example.com'
        ]);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    /**
     * Test: Usuario puede resetear contraseña con token válido
     */
    public function test_user_can_reset_password_with_valid_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('OldPassword123!')
        ]);

        $token = 'test-token-123';

        // Crear token en BD
        DB::table('password_reset_tokens')->insert([
            'email' => 'test@example.com',
            'token' => Hash::make($token),
            'created_at' => Carbon::now()
        ]);

        $response = $this->postJson('/api/password/reset', [
            'token' => $token,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success'
                 ]);

        // Verificar que la contraseña cambió
        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));

        // Verificar que el token fue eliminado
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'test@example.com'
        ]);
    }

    /**
     * Test: Usuario no puede resetear con token inválido
     */
    public function test_user_cannot_reset_password_with_invalid_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        $response = $this->postJson('/api/password/reset', [
            'token' => 'invalid-token',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!'
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     'status' => 'error'
                 ]);
    }

    /**
     * Test: Usuario no puede resetear con token expirado
     */
    public function test_user_cannot_reset_password_with_expired_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        $token = 'test-token-123';

        // Crear token expirado (más de 60 minutos)
        DB::table('password_reset_tokens')->insert([
            'email' => 'test@example.com',
            'token' => Hash::make($token),
            'created_at' => Carbon::now()->subMinutes(61)
        ]);

        $response = $this->postJson('/api/password/reset', [
            'token' => $token,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!'
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     'status' => 'error'
                 ]);
    }

    /**
     * Test: Rate limiting funciona (3 intentos por hora)
     */
    public function test_rate_limiting_works()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        // Hacer 3 solicitudes (límite)
        for ($i = 0; $i < 3; $i++) {
            $response = $this->postJson('/api/password/forgot', [
                'email' => 'test@example.com'
            ]);
            $response->assertStatus(200);
        }

        // La 4ta solicitud debe ser bloqueada
        $response = $this->postJson('/api/password/forgot', [
            'email' => 'test@example.com'
        ]);

        $response->assertStatus(429); // Too Many Requests
    }

    /**
     * Test: Validación de contraseña funciona
     */
    public function test_password_validation_works()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        $token = 'test-token-123';

        DB::table('password_reset_tokens')->insert([
            'email' => 'test@example.com',
            'token' => Hash::make($token),
            'created_at' => Carbon::now()
        ]);

        // Contraseña muy corta
        $response = $this->postJson('/api/password/reset', [
            'token' => $token,
            'password' => 'short',
            'password_confirmation' => 'short'
        ]);
        $response->assertStatus(422);

        // Sin mayúscula
        $response = $this->postJson('/api/password/reset', [
            'token' => $token,
            'password' => 'password123!',
            'password_confirmation' => 'password123!'
        ]);
        $response->assertStatus(422);

        // Sin número
        $response = $this->postJson('/api/password/reset', [
            'token' => $token,
            'password' => 'Password!',
            'password_confirmation' => 'Password!'
        ]);
        $response->assertStatus(422);

        // Sin carácter especial
        $response = $this->postJson('/api/password/reset', [
            'token' => $token,
            'password' => 'Password123',
            'password_confirmation' => 'Password123'
        ]);
        $response->assertStatus(422);
    }

    /**
     * Test: Tokens de Sanctum son invalidados al resetear
     */
    public function test_sanctum_tokens_are_invalidated_on_reset()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        // Crear token de Sanctum
        $sanctumToken = $user->createToken('test-device')->plainTextToken;

        $resetToken = 'test-token-123';

        DB::table('password_reset_tokens')->insert([
            'email' => 'test@example.com',
            'token' => Hash::make($resetToken),
            'created_at' => Carbon::now()
        ]);

        // Resetear contraseña
        $this->postJson('/api/password/reset', [
            'token' => $resetToken,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!'
        ]);

        // Verificar que los tokens fueron eliminados
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id
        ]);
    }

    /**
     * Test: No revela si el email existe
     */
    public function test_does_not_reveal_if_email_exists()
    {
        // Email que no existe
        $response = $this->postJson('/api/password/forgot', [
            'email' => 'nonexistent@example.com'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success'
                 ]);

        // Mismo mensaje que cuando existe
        $this->assertStringContainsString('Si el email existe', $response->json('message'));
    }

    /**
     * Test: Solo un token activo por usuario
     */
    public function test_only_one_token_per_user()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        // Primera solicitud
        $this->postJson('/api/password/forgot', [
            'email' => 'test@example.com'
        ]);

        $firstTokenCount = DB::table('password_reset_tokens')
            ->where('email', 'test@example.com')
            ->count();

        // Segunda solicitud (debe reemplazar el primero)
        $this->postJson('/api/password/forgot', [
            'email' => 'test@example.com'
        ]);

        $secondTokenCount = DB::table('password_reset_tokens')
            ->where('email', 'test@example.com')
            ->count();

        // Debe haber solo 1 token
        $this->assertEquals(1, $firstTokenCount);
        $this->assertEquals(1, $secondTokenCount);
    }
}
