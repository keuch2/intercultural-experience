<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AppBanner;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Banners del Inicio de la app (pedido de IE, nov 2026): imagen o texto, con enlace, público,
 * programa, orden y vigencia.
 */
class AppBannerController extends Controller
{
    public function index()
    {
        $banners = AppBanner::with(['linkProgram', 'program'])->orderBy('sort_order')->orderByDesc('id')->get();

        return view('admin.app-banners.index', compact('banners'));
    }

    public function create()
    {
        $banner = new AppBanner(['type' => 'text', 'audience' => 'all', 'link_type' => 'none', 'background_color' => '#E52224',
            'is_active' => true, 'sort_order' => (int) AppBanner::max('sort_order') + 1]);

        return view('admin.app-banners.form', ['banner' => $banner, 'programs' => $this->programs()]);
    }

    public function store(Request $request)
    {
        $banner = new AppBanner(['created_by' => $request->user()->id]);
        $this->save($request, $banner);

        return redirect()->route('admin.app-banners.index')->with('success', 'Banner creado.');
    }

    public function edit(AppBanner $appBanner)
    {
        return view('admin.app-banners.form', ['banner' => $appBanner, 'programs' => $this->programs()]);
    }

    public function update(Request $request, AppBanner $appBanner)
    {
        $this->save($request, $appBanner);

        return redirect()->route('admin.app-banners.index')->with('success', 'Banner actualizado.');
    }

    public function toggle(AppBanner $appBanner)
    {
        $appBanner->update(['is_active' => ! $appBanner->is_active]);

        return back()->with('success', $appBanner->is_active ? 'Banner activado.' : 'Banner desactivado.');
    }

    public function destroy(AppBanner $appBanner)
    {
        if ($appBanner->image_path) {
            Storage::disk('public')->delete($appBanner->image_path);
        }
        $appBanner->delete();
        ActivityLog::log('settings')->withAction('banner_deleted')->withProperties(['banner_id' => $appBanner->id, 'title' => $appBanner->title])
            ->log("Banner eliminado: {$appBanner->title}");

        return redirect()->route('admin.app-banners.index')->with('success', 'Banner eliminado.');
    }

    private function save(Request $request, AppBanner $banner): void
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(AppBanner::TYPES))],
            'image' => [Rule::requiredIf($request->input('type') === 'image' && ! $banner->image_path), 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'title' => [Rule::requiredIf($request->input('type') === 'text'), 'nullable', 'string', 'max:120'],
            'subtitle' => ['nullable', 'string', 'max:300'],
            'badge' => ['nullable', 'string', 'max:40'],
            'background_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'button_label' => ['nullable', 'string', 'max:40'],
            'link_type' => ['required', Rule::in(array_keys(AppBanner::LINK_TYPES))],
            'link_program_id' => ['required_if:link_type,program', 'nullable', 'exists:programs,id'],
            'link_url' => ['required_if:link_type,url', 'nullable', 'url:https,http', 'max:500'],
            'audience' => ['required', Rule::in(array_keys(AppBanner::AUDIENCES))],
            'program_id' => ['nullable', 'exists:programs,id'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'image.required' => 'Subí la imagen del banner.',
            'title.required' => 'El banner de texto necesita un título.',
            'link_program_id.required_if' => 'Elegí el programa al que lleva el banner.',
            'link_url.required_if' => 'Ingresá el enlace web.',
            'ends_at.after_or_equal' => 'La fecha de fin debe ser posterior al inicio.',
        ], [
            'title' => 'título', 'subtitle' => 'texto', 'background_color' => 'color', 'link_url' => 'enlace web', 'image' => 'imagen',
        ]);

        if ($request->hasFile('image')) {
            if ($banner->image_path) {
                Storage::disk('public')->delete($banner->image_path);
            }
            $data['image_path'] = $request->file('image')->store('banners', 'public');
        }
        unset($data['image']);

        // Un banner con programa de destino solo tiene sentido para participantes con sesión.
        if ($data['audience'] === 'public') {
            $data['program_id'] = null;
        }
        if ($data['link_type'] !== 'program') {
            $data['link_program_id'] = null;
        }
        if ($data['link_type'] !== 'url') {
            $data['link_url'] = null;
        }
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        $banner->fill($data)->save();
        ActivityLog::log('settings')->withAction('banner_saved')->withProperties(['banner_id' => $banner->id, 'title' => $banner->title])
            ->causedBy($request->user())->log("Banner guardado: {$banner->title}");
    }

    private function programs()
    {
        return Program::where('is_active', true)->orderBy('name')->get(['id', 'name', 'is_available_in_app']);
    }
}
