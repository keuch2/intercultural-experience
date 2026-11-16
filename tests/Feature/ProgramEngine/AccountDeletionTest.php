<?php

namespace Tests\Feature\ProgramEngine;

use App\Models\Notification;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

class AccountDeletionTest extends EngineTestCase
{
    public function test_deleting_the_app_account_only_removes_app_access_and_keeps_all_records(): void
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
        $processStatus = $process->status;

        Sanctum::actingAs($user);
        $this->deleteJson('/api/me', ['password' => 'incorrecta'])->assertStatus(422);
        $this->assertNull($user->fresh()->app_account_deleted_at);

        $this->deleteJson('/api/me', ['password' => 'secreto123'])->assertOk()->assertJsonPath('status', 'success');

        // Se conserva todo: ficha, archivos, documentos, proceso, postulación y pago.
        $fresh = $user->fresh();
        $this->assertNotNull($fresh->app_account_deleted_at);
        $this->assertSame('Ana Pérez', $fresh->name);
        $this->assertSame('ana@example.com', $fresh->email);
        $this->assertSame('0981123456', $fresh->phone);
        $this->assertSame('AB123', $fresh->passport_number);
        Storage::disk('public')->assertExists('profile_photos/ana.jpg');
        Storage::disk('public')->assertExists('test/passport-0.pdf');
        $this->assertDatabaseHas('program_documents', ['program_process_id' => $process->id]);
        $this->assertSame($processStatus, $process->fresh()->status);
        $this->assertDatabaseHas('applications', ['id' => $process->application_id, 'user_id' => $user->id]);
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'verified']);

        // Solo se pierde el acceso a la app.
        $this->assertSame(0, $fresh->tokens()->count());
        $this->assertTrue(Notification::where('user_id', $admin->id)->where('title', 'Cuenta de la app eliminada')->exists());

        $this->app['auth']->forgetGuards();
        $this->postJson('/api/login', ['email' => 'ana@example.com', 'password' => 'secreto123'])
            ->assertStatus(403)->assertJsonPath('message', User::APP_ACCOUNT_DELETED_MESSAGE);
        $this->postJson('/api/auth/check-email', ['email' => 'ana@example.com'])
            ->assertOk()->assertJsonPath('state', 'app_account_deleted');
    }

    public function test_admin_restores_app_access_and_participant_creates_a_new_password(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create([
            'role' => 'user', 'email' => 'ana@example.com', 'app_account_deleted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.participants.show', $user->id))
            ->assertOk()->assertSee('Cuenta de la app eliminada')->assertSee('Restaurar acceso a la app');

        $this->actingAs($admin)
            ->post(route('admin.participants.restore-app-access', $user->id))
            ->assertRedirect();

        $fresh = $user->fresh();
        $this->assertNull($fresh->app_account_deleted_at);
        $this->assertTrue((bool) $fresh->requires_password_setup);

        $this->postJson('/api/auth/check-email', ['email' => 'ana@example.com'])
            ->assertOk()->assertJsonPath('state', 'password_setup_required');
    }

    public function test_staff_accounts_cannot_be_deleted_from_the_app(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'password' => bcrypt('secreto123')]);
        Sanctum::actingAs($admin);

        $this->deleteJson('/api/me', ['password' => 'secreto123'])->assertForbidden();
        $this->assertNull($admin->fresh()->app_account_deleted_at);
    }
}
