<?php

namespace Tests\Feature\Admin;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_configures_whatsapp_and_app_receives_number_and_message(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.settings.whatsapp'))->assertOk()->assertSee('{nombre}');
        $this->actingAs($admin)->post(route('admin.settings.whatsapp.update'), [
            'whatsapp_support_number' => '+595981111222', 'whatsapp_support_enabled' => 1,
            'whatsapp_welcome_message' => 'Hola, soy {nombre} del programa {programa}. Necesito ayuda.',
        ])->assertRedirect(route('admin.settings.whatsapp'));

        $this->assertTrue((bool) SystemSetting::where('key', 'whatsapp_welcome_message')->value('is_public'));

        $this->getJson('/api/settings/whatsapp')->assertOk()
            ->assertJsonPath('data.whatsapp_support_number', '+595981111222')
            ->assertJsonPath('data.whatsapp_support_message', 'Hola, soy {nombre} del programa {programa}. Necesito ayuda.');

        $this->actingAs($admin)->get(route('admin.settings.index'))->assertOk()->assertSee(route('admin.settings.whatsapp'), false);
    }
}
