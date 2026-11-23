<?php

namespace Tests\Feature;

use App\Models\AppBanner;
use App\Models\Application;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Pedido de IE (nov 2026): banners del Inicio de la app gestionados desde el admin (imagen o texto).
 */
class AppBannerTest extends TestCase
{
    use RefreshDatabase;

    private function program(string $name): Program
    {
        return Program::create(['name' => $name, 'description' => 'x', 'country' => 'USA', 'main_category' => 'IE',
            'subcategory' => $name, 'is_active' => true, 'is_available_in_app' => true]);
    }

    private function banner(array $attrs = []): AppBanner
    {
        return AppBanner::create(array_merge(['type' => 'text', 'title' => 'Banner', 'audience' => 'all', 'is_active' => true], $attrs));
    }

    public function test_public_and_participant_feeds_respect_audience_program_and_schedule(): void
    {
        $wt = $this->program('Work & Travel');
        $other = $this->program('Teachers');
        $this->banner(['title' => 'Todos', 'sort_order' => 2]);
        $this->banner(['title' => 'Publico', 'audience' => 'public', 'sort_order' => 1]);
        $this->banner(['title' => 'Participantes', 'audience' => 'participants']);
        $this->banner(['title' => 'Solo W&T', 'audience' => 'participants', 'program_id' => $wt->id]);
        $this->banner(['title' => 'Solo Teachers', 'audience' => 'participants', 'program_id' => $other->id]);
        $this->banner(['title' => 'Inactivo', 'is_active' => false]);
        $this->banner(['title' => 'Futuro', 'starts_at' => now()->addDay()]);
        $this->banner(['title' => 'Vencido', 'ends_at' => now()->subMinute()]);

        $public = collect($this->getJson('/api/public/banners')->assertOk()->json('data'))->pluck('title')->all();
        $this->assertSame(['Publico', 'Todos'], $public);

        $user = User::factory()->create(['role' => 'user']);
        Application::create(['user_id' => $user->id, 'program_id' => $wt->id, 'status' => 'pending']);
        $mine = collect($this->actingAs($user, 'sanctum')->getJson('/api/banners')->assertOk()->json('data'))->pluck('title')->sort()->values()->all();
        $this->assertSame(['Participantes', 'Solo W&T', 'Todos'], $mine);
    }

    public function test_link_and_image_are_exposed(): void
    {
        $program = $this->program('Au Pair');
        $this->banner(['link_type' => 'program', 'link_program_id' => $program->id, 'button_label' => 'Conocé más', 'sort_order' => 1]);
        $this->banner(['type' => 'image', 'image_path' => 'banners/x.jpg', 'link_type' => 'url', 'link_url' => 'https://ie.org.py', 'sort_order' => 2]);

        $data = $this->getJson('/api/public/banners')->json('data');
        $this->assertSame(['type' => 'program', 'program_id' => $program->id], $data[0]['link']);
        $this->assertSame(['type' => 'url', 'url' => 'https://ie.org.py'], $data[1]['link']);
        $this->assertStringEndsWith('/storage/banners/x.jpg', $data[1]['image_url']);
    }

    public function test_admin_creates_edits_toggles_and_deletes_banners(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $program = $this->program('Work & Travel');

        $this->actingAs($admin)->get(route('admin.app-banners.index'))->assertOk()->assertSee('Banners de la app');
        $this->actingAs($admin)->get(route('admin.app-banners.create'))->assertOk()->assertSee('Vista previa');

        // Validación: imagen obligatoria para banners de imagen; programa para el enlace.
        $this->actingAs($admin)->post(route('admin.app-banners.store'), ['type' => 'image', 'link_type' => 'program',
            'audience' => 'all', 'background_color' => '#E52224'])->assertSessionHasErrors(['image', 'link_program_id']);

        $this->actingAs($admin)->post(route('admin.app-banners.store'), [
            'type' => 'image', 'title' => 'Temporada 2027', 'image' => UploadedFile::fake()->image('b.jpg', 1200, 600),
            'background_color' => '#E52224', 'link_type' => 'program', 'link_program_id' => $program->id,
            'link_url' => 'https://ignorado.com', 'audience' => 'public', 'program_id' => $program->id, 'sort_order' => 3, 'is_active' => '1',
        ])->assertRedirect(route('admin.app-banners.index'));

        $banner = AppBanner::sole();
        Storage::disk('public')->assertExists($banner->image_path);
        $this->assertNull($banner->link_url);
        $this->assertNull($banner->program_id); // público: sin filtro de programa
        $this->assertTrue($banner->is_active);

        $this->actingAs($admin)->get(route('admin.app-banners.edit', $banner))->assertOk()->assertSee('Temporada 2027');
        $this->actingAs($admin)->put(route('admin.app-banners.update', $banner), [
            'type' => 'text', 'title' => 'Nuevo texto', 'background_color' => '#123456', 'link_type' => 'none', 'audience' => 'all', 'is_active' => '1',
        ])->assertRedirect();
        $this->assertSame('Nuevo texto', $banner->fresh()->title);
        $this->assertNull($banner->fresh()->link_program_id);

        $this->actingAs($admin)->post(route('admin.app-banners.toggle', $banner))->assertRedirect();
        $this->assertFalse($banner->fresh()->is_active);

        $path = $banner->image_path;
        $this->actingAs($admin)->delete(route('admin.app-banners.destroy', $banner))->assertRedirect();
        $this->assertSame(0, AppBanner::count());
        Storage::disk('public')->assertMissing($path);
    }

    public function test_participants_cannot_manage_banners(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user)->get(route('admin.app-banners.index'))->assertRedirect();
    }
}
