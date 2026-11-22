<?php

namespace Tests\Feature\Admin;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Reporte del cliente (nov 2026): la web mostraba 3 h de diferencia con Paraguay.
 */
class SystemTimezoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_timezone_is_paraguay(): void
    {
        $this->assertSame('America/Asuncion', config('app.timezone'));
    }

    public function test_admin_can_change_the_timezone_from_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.settings.index'))
            ->assertOk()->assertSee('Zona horaria del sistema');

        $this->actingAs($admin)->post(route('admin.settings.timezone.update'), ['timezone' => 'America/Sao_Paulo'])
            ->assertRedirect(route('admin.settings.index'));
        $this->assertSame('America/Sao_Paulo', SystemSetting::get(SystemSetting::TIMEZONE_KEY));

        $this->actingAs($admin)->post(route('admin.settings.timezone.update'), ['timezone' => 'Marte/Olympus'])
            ->assertSessionHasErrors('timezone');
        $this->assertSame('America/Sao_Paulo', SystemSetting::get(SystemSetting::TIMEZONE_KEY));
    }

    public function test_migration_shifts_only_system_generated_timestamps(): void
    {
        $user = User::factory()->create();
        DB::table('users')->where('id', $user->id)->update(['created_at' => '2026-11-20 17:08:18']);
        $system = DB::table('activity_logs')->insertGetId(['log_name' => 'user', 'description' => 'x', 'created_at' => '2026-11-20 17:08:18']);
        $dbDefault = DB::table('activity_logs')->insertGetId(['log_name' => 'admin_activity', 'description' => 'x', 'created_at' => '2026-11-20 14:08:17']);

        $migration = require database_path('migrations/2026_11_22_100000_shift_system_timestamps_to_asuncion.php');
        $migration->up();

        $this->assertSame('2026-11-20 14:08:18', DB::table('users')->where('id', $user->id)->value('created_at'));
        $this->assertSame('2026-11-20 14:08:18', DB::table('activity_logs')->where('id', $system)->value('created_at'));
        $this->assertSame('2026-11-20 14:08:17', DB::table('activity_logs')->where('id', $dbDefault)->value('created_at'));

        $migration->down();
        $this->assertSame('2026-11-20 17:08:18', DB::table('users')->where('id', $user->id)->value('created_at'));
    }
}
