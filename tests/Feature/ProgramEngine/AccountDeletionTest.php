<?php

namespace Tests\Feature\ProgramEngine;

use App\Models\Notification;
use App\Models\Payment;
use App\Models\ProgramProcess;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

class AccountDeletionTest extends EngineTestCase
{
    public function test_participant_deletes_account_and_personal_data_is_anonymized(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $user = User::factory()->create([
            'role' => 'user', 'name' => 'Ana Pérez', 'email' => 'ana@example.com', 'password' => bcrypt('secreto123'),
            'phone' => '0981123456', 'passport_number' => 'AB123', 'profile_photo' => 'profile_photos/ana.jpg',
        ]);
        Storage::disk('public')->put('profile_photos/ana.jpg', 'x');
        Storage::disk('public')->put('test/passport-0.pdf', 'x');

        $process = $this->processFor($user, $this->engineProgram());
        $this->approvedDoc($process, 'passport', 'application');
        $payment = Payment::create([
            'user_id' => $user->id, 'application_id' => $process->application_id, 'program_id' => $process->program_id,
            'amount' => 100, 'status' => 'verified', 'concept' => 'Inscripción', 'payment_method' => 'transfer',
        ]);
        $user->createToken('app');

        Sanctum::actingAs($user);
        $this->deleteJson('/api/me', ['password' => 'incorrecta'])->assertStatus(422);
        $this->assertSame('Ana Pérez', $user->fresh()->name);

        $this->deleteJson('/api/me', ['password' => 'secreto123'])->assertOk()->assertJsonPath('status', 'success');

        $fresh = $user->fresh();
        $this->assertSame('Usuario eliminado', $fresh->name);
        $this->assertStringEndsWith('@ie.invalid', $fresh->email);
        $this->assertNull($fresh->phone);
        $this->assertNull($fresh->passport_number);
        $this->assertNull($fresh->getRawOriginal('profile_photo'));
        $this->assertNotNull($fresh->anonymized_at);
        $this->assertSame(0, $fresh->tokens()->count());

        Storage::disk('public')->assertMissing('profile_photos/ana.jpg');
        Storage::disk('public')->assertMissing('test/passport-0.pdf');
        $this->assertDatabaseMissing('program_documents', ['program_process_id' => $process->id]);
        $this->assertSame(ProgramProcess::STATUS_CANCELLED, $process->fresh()->status);

        // Postulación y pago quedan para la contabilidad de IE.
        $this->assertDatabaseHas('applications', ['id' => $process->application_id, 'user_id' => $user->id]);
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'verified']);

        $this->assertTrue(Notification::where('user_id', $admin->id)->where('title', 'Cuenta eliminada por un participante')->exists());

        // Ya no puede iniciar sesión con sus credenciales anteriores.
        $this->postJson('/api/login', ['email' => 'ana@example.com', 'password' => 'secreto123'])->assertStatus(401);
    }

    public function test_staff_accounts_cannot_be_deleted_from_the_app(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'password' => bcrypt('secreto123')]);
        Sanctum::actingAs($admin);

        $this->deleteJson('/api/me', ['password' => 'secreto123'])->assertForbidden();
        $this->assertNull($admin->fresh()->anonymized_at);
    }
}
