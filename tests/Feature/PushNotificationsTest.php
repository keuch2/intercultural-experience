<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\DeviceToken;
use App\Models\Notification;
use App\Models\NotificationCampaign;
use App\Models\NotificationType;
use App\Models\Program;
use App\Models\PushDelivery;
use App\Models\User;
use App\Models\UserNotificationPreference;
use App\Services\Notifications\PushSender;
use App\Services\ProgramEngine\Notifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Fase 6 (nov 2026): notificaciones push (Expo), preferencias por categoría y avisos especiales.
 */
class PushNotificationsTest extends TestCase
{
    use RefreshDatabase;

    private function participant(array $attrs = []): User
    {
        return User::factory()->create(array_merge(['role' => 'user'], $attrs));
    }

    private function device(User $user, string $suffix = 'abc'): DeviceToken
    {
        return DeviceToken::create(['user_id' => $user->id, 'token' => "ExponentPushToken[{$suffix}]", 'platform' => 'ios', 'last_seen_at' => now()]);
    }

    private function program(string $name): Program
    {
        return Program::create(['name' => $name, 'description' => 'x', 'country' => 'USA', 'main_category' => 'IE',
            'subcategory' => $name, 'is_active' => true, 'is_available_in_app' => true]);
    }

    private function notify(User $user, ?string $type): Notification
    {
        return app(Notifier::class)->toUser($user, 'Título', 'Mensaje', 'documents', $type);
    }

    public function test_push_status_follows_type_config_preferences_and_mandatory_rules(): void
    {
        $user = $this->participant();

        $this->assertNull($this->notify($user, null)->push_status, 'sin tipo: solo in-app');
        $this->assertSame('pending', $this->notify($user, 'document_approved')->push_status);

        UserNotificationPreference::create(['user_id' => $user->id, 'category' => 'documents', 'push_enabled' => false]);
        $this->assertSame('muted', $this->notify($user, 'document_approved')->push_status);
        $this->assertSame('pending', $this->notify($user, 'document_rejected')->push_status, 'obligatorio: ignora el silencio');

        NotificationType::where('key', 'document_rejected')->first()->update(['push_enabled' => false]);
        $this->assertSame('disabled', $this->notify($user, 'document_rejected')->push_status);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->assertNull($this->notify($admin, 'document_approved')->push_status, 'el staff no recibe push');

        $closed = $this->participant(['app_account_deleted_at' => now()]);
        $this->assertNull($this->notify($closed, 'document_approved')->push_status);

        // Silenciar solo corta la push: el aviso sigue visible en la app.
        $types = collect($this->actingAs($user, 'sanctum')->getJson('/api/notifications')->assertOk()->json('data'))->pluck('notification_type');
        $this->assertCount(5, $types);
        $this->assertContains('document_rejected', $types);
    }

    public function test_sender_delivers_to_every_device_and_handles_expo_errors(): void
    {
        $user = $this->participant();
        $this->device($user, 'one');
        $this->device($user, 'gone');
        $lonely = $this->participant();

        Http::fake(['exp.host/--/api/v2/push/send' => Http::response(['data' => [
            ['status' => 'ok', 'id' => 'ticket-1'],
            ['status' => 'error', 'message' => 'not registered', 'details' => ['error' => 'DeviceNotRegistered']],
        ]])]);

        $sent = app(Notifier::class)->toUser($user, 'Pago rechazado', 'Motivo: ilegible', 'payment', 'payment_rejected', ['target' => 'payments']);
        $none = $this->notify($lonely, 'document_approved');

        $stats = app(PushSender::class)->sendPending();
        $this->assertSame(1, $stats['sent']);
        $this->assertSame(1, $stats['no_device']);
        $this->assertSame('sent', $sent->fresh()->push_status);
        $this->assertSame('no_device', $none->fresh()->push_status);
        $this->assertSame(['ExponentPushToken[one]'], DeviceToken::pluck('token')->all(), 'el token dado de baja se borra');

        Http::assertSent(function ($request) {
            $first = $request->data()[0];

            return $first['title'] === 'Pago rechazado' && $first['channelId'] === 'payments'
                && $first['data']['target'] === 'payments' && $first['data']['notification_type'] === 'payment_rejected';
        });
        $this->assertSame(2, PushDelivery::count());
    }

    public function test_network_failure_keeps_push_pending_and_receipts_remove_dead_tokens(): void
    {
        $user = $this->participant();
        $token = $this->device($user);
        $n = $this->notify($user, 'document_approved');

        Http::fake([
            // 1.ª corrida: Expo caído (el cliente reintenta una vez); 2.ª corrida: ok.
            'exp.host/--/api/v2/push/send' => Http::sequence()->push('down', 503)->push('down', 503)
                ->push(['data' => [['status' => 'ok', 'id' => 'tk-9']]]),
            'exp.host/--/api/v2/push/getReceipts' => Http::response(['data' => ['tk-9' => ['status' => 'error', 'details' => ['error' => 'DeviceNotRegistered']]]]),
        ]);
        $this->assertSame(1, app(PushSender::class)->sendPending()['retry']);
        $this->assertSame('pending', $n->fresh()->push_status);

        app(PushSender::class)->sendPending();
        $this->assertSame('sent', $n->fresh()->push_status);

        $this->travel(20)->minutes();
        $stats = app(PushSender::class)->checkReceipts();
        $this->assertSame(1, $stats['removed_tokens']);
        $this->assertNull($token->fresh());
        $this->assertSame('error', PushDelivery::first()->receipt_status);
    }

    public function test_device_token_api_registers_reassigns_and_removes(): void
    {
        $ana = $this->participant();
        $beto = $this->participant();
        $token = 'ExponentPushToken[device-1]';

        $this->actingAs($ana, 'sanctum')->postJson('/api/me/device-tokens', ['token' => 'nope'])->assertStatus(422);
        $this->actingAs($ana, 'sanctum')->postJson('/api/me/device-tokens', ['token' => $token, 'platform' => 'android', 'app_version' => '1.1.0'])->assertOk();
        $this->actingAs($beto, 'sanctum')->postJson('/api/me/device-tokens', ['token' => $token, 'platform' => 'android'])->assertOk();
        $this->assertSame($beto->id, DeviceToken::sole()->user_id, 'el dispositivo pasa a quien inició sesión');

        $this->actingAs($beto, 'sanctum')->deleteJson('/api/me/device-tokens', ['token' => $token])->assertOk();
        $this->assertSame(0, DeviceToken::count());
    }

    public function test_preferences_api_lists_categories_with_mandatory_notices_and_saves(): void
    {
        $user = $this->participant();

        $res = $this->actingAs($user, 'sanctum')->getJson('/api/me/notification-preferences')->assertOk();
        $categories = collect($res->json('data.categories'))->keyBy('key');
        $this->assertCount(7, $categories);
        $this->assertTrue($categories['news']['push_enabled']);
        $this->assertContains('Documento rechazado', $categories['documents']['mandatory']);

        $this->actingAs($user, 'sanctum')->putJson('/api/me/notification-preferences', ['preferences' => ['news' => false, 'bogus' => false]])
            ->assertOk()->assertJsonPath('data.categories.6.push_enabled', false);
        $this->assertSame(1, UserNotificationPreference::where('user_id', $user->id)->count());
    }

    public function test_admin_sends_segmented_campaign_now_with_link_and_important_flag(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $wt = $this->program('Work & Travel');
        $other = $this->program('Teachers');
        $inWt = $this->participant(['name' => 'Ana WT']);
        $muted = $this->participant(['name' => 'Beto WT']);
        $inOther = $this->participant();
        $rejected = $this->participant();
        Application::create(['user_id' => $inWt->id, 'program_id' => $wt->id, 'status' => 'approved']);
        Application::create(['user_id' => $muted->id, 'program_id' => $wt->id, 'status' => 'pending']);
        Application::create(['user_id' => $inOther->id, 'program_id' => $other->id, 'status' => 'approved']);
        Application::create(['user_id' => $rejected->id, 'program_id' => $wt->id, 'status' => 'rejected']);
        UserNotificationPreference::create(['user_id' => $muted->id, 'category' => 'news', 'push_enabled' => false]);

        $form = [
            'title' => 'Feria de empleadores', 'message' => 'Te esperamos el sábado.', 'link_type' => 'program', 'link_program_id' => $wt->id,
            'channel' => 'push', 'audience_mode' => 'segment', 'program_ids' => [$wt->id], 'send_mode' => 'now',
        ];
        $this->actingAs($admin)->postJson(route('admin.notification-campaigns.preview'), $form)->assertOk()->assertJsonPath('count', 2);

        $this->actingAs($admin)->get(route('admin.notification-campaigns.create'))->assertOk()->assertSee('Destinatarios');
        $this->actingAs($admin)->post(route('admin.notification-campaigns.store'), $form)->assertRedirect();

        $campaign = NotificationCampaign::sole();
        $this->assertSame(NotificationCampaign::STATUS_SENT, $campaign->status);
        $this->assertSame(2, $campaign->recipients_count);
        $byUser = Notification::where('campaign_id', $campaign->id)->get()->keyBy('user_id');
        $this->assertSame(['target' => 'program', 'program_id' => $wt->id], $byUser[$inWt->id]->data);
        $this->assertSame('pending', $byUser[$inWt->id]->push_status);
        $this->assertSame('muted', $byUser[$muted->id]->push_status, 'no importante: respeta el silencio');

        // "Importante" ignora el silencio.
        $this->actingAs($admin)->post(route('admin.notification-campaigns.store'), ['is_important' => '1', 'title' => 'Urgente'] + $form)->assertRedirect();
        $urgent = NotificationCampaign::where('title', 'Urgente')->sole();
        $this->assertSame('pending', Notification::where('campaign_id', $urgent->id)->where('user_id', $muted->id)->value('push_status'));

        $this->actingAs($admin)->get(route('admin.notification-campaigns.index'))->assertOk()->assertSee('Feria de empleadores');
        $this->actingAs($admin)->get(route('admin.notification-campaigns.show', $campaign))->assertOk()->assertSee('Ana WT');
    }

    public function test_scheduled_campaign_waits_until_due_and_can_be_cancelled(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = $this->participant(['name' => 'Carla']);
        $base = ['message' => 'Hola', 'link_type' => 'screen', 'link_target' => 'payments', 'channel' => 'in_app', 'audience_mode' => 'users', 'user_ids' => [$user->id], 'send_mode' => 'scheduled'];

        $this->actingAs($admin)->post(route('admin.notification-campaigns.store'), $base + ['title' => 'Pasado', 'scheduled_at' => now()->subHour()->format('Y-m-d H:i')])
            ->assertSessionHasErrors('scheduled_at');
        $this->actingAs($admin)->post(route('admin.notification-campaigns.store'), $base + ['title' => 'Cuotas', 'scheduled_at' => now()->addHour()->format('Y-m-d H:i')])->assertRedirect();
        $this->actingAs($admin)->post(route('admin.notification-campaigns.store'), $base + ['title' => 'Cancelado', 'scheduled_at' => now()->addHour()->format('Y-m-d H:i')])->assertRedirect();

        $cancelled = NotificationCampaign::where('title', 'Cancelado')->sole();
        $this->actingAs($admin)->post(route('admin.notification-campaigns.cancel', $cancelled))->assertRedirect();
        $this->actingAs($admin)->get(route('admin.notification-campaigns.edit', $cancelled))->assertNotFound();

        $this->artisan('notifications:dispatch')->assertSuccessful();
        $this->assertSame(0, Notification::count());

        $this->travel(61)->minutes();
        $this->artisan('notifications:dispatch')->assertSuccessful();
        $n = Notification::sole();
        $this->assertSame('Cuotas', $n->title);
        $this->assertNull($n->push_status, 'canal solo app');
        $this->assertSame(['target' => 'payments'], $n->data);
        $this->assertSame(NotificationCampaign::STATUS_CANCELLED, $cancelled->fresh()->status);

        $this->actingAs($admin)->getJson(route('admin.notification-campaigns.users', ['q' => 'Carl']))->assertJsonPath('data.0.id', $user->id);
    }

    public function test_admin_configures_types_and_participants_cannot_manage_campaigns(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.notification-types.index'))->assertOk()->assertSee('Documento rechazado');
        $this->actingAs($admin)->put(route('admin.notification-types.update'), [
            'push' => ['document_approved' => '1'], 'mandatory' => ['document_approved' => '1'],
        ])->assertRedirect();
        $settings = NotificationType::settings();
        $this->assertTrue($settings['document_approved']['mandatory']);
        $this->assertFalse($settings['document_rejected']['push_enabled']);

        $user = $this->participant();
        $this->actingAs($user)->get(route('admin.notification-campaigns.index'))->assertRedirect();
    }

    public function test_closing_the_app_account_removes_devices(): void
    {
        $user = $this->participant();
        $this->device($user);
        app(\App\Services\AccountDeletionService::class)->closeAppAccount($user);
        $this->assertSame(0, DeviceToken::count());
    }
}
