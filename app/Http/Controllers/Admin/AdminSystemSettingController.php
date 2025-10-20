<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SystemSetting;

class AdminSystemSettingController extends Controller
{
    /**
     * Muestra la página de configuraciones del sistema
     */
    public function index()
    {
        $settings = SystemSetting::orderBy('group')->orderBy('key')->get()->groupBy('group');
        
        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Muestra configuraciones específicas de WhatsApp
     */
    public function whatsapp()
    {
        $whatsappSettings = SystemSetting::where('group', 'whatsapp')->get();
        
        return view('admin.settings.whatsapp', compact('whatsappSettings'));
    }

    /**
     * Actualiza configuraciones de WhatsApp
     */
    public function updateWhatsapp(Request $request)
    {
        $request->validate([
            'whatsapp_support_number' => 'required|string|max:20',
            'whatsapp_support_enabled' => 'boolean',
            'whatsapp_welcome_message' => 'required|string|max:500',
        ]);

        // Actualizar número de WhatsApp
        SystemSetting::set(
            'whatsapp_support_number',
            $request->whatsapp_support_number,
            'string',
            'whatsapp',
            'Número de WhatsApp para soporte al cliente',
            true
        );

        // Actualizar estado habilitado/deshabilitado
        SystemSetting::set(
            'whatsapp_support_enabled',
            $request->has('whatsapp_support_enabled'),
            'boolean',
            'whatsapp',
            'Habilitar/deshabilitar soporte por WhatsApp',
            true
        );

        // Actualizar mensaje de bienvenida
        SystemSetting::set(
            'whatsapp_welcome_message',
            $request->whatsapp_welcome_message,
            'string',
            'whatsapp',
            'Mensaje de bienvenida para WhatsApp',
            false
        );

        return redirect()->route('admin.settings.whatsapp')
            ->with('success', 'Configuraciones de WhatsApp actualizadas correctamente.');
    }

    /**
     * Actualiza una configuración específica
     */
    public function update(Request $request, $key)
    {
        $setting = SystemSetting::where('key', $key)->firstOrFail();
        
        $request->validate([
            'value' => 'required',
        ]);

        $value = $request->value;
        
        // Convertir valor según el tipo
        if ($setting->type === 'boolean') {
            $value = $request->has('value') && $request->value === '1';
        }

        SystemSetting::set(
            $setting->key,
            $value,
            $setting->type,
            $setting->group,
            $setting->description,
            $setting->is_public
        );

        return redirect()->back()
            ->with('success', 'Configuración actualizada correctamente.');
    }

    /**
     * Muestra configuraciones generales
     */
    public function general()
    {
        $generalSettings = SystemSetting::where('group', 'general')->get();
        $contactSettings = SystemSetting::where('group', 'contact')->get();
        
        return view('admin.settings.general', compact('generalSettings', 'contactSettings'));
    }

    /**
     * Actualiza configuraciones generales
     */
    public function updateGeneral(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:100',
            'app_version' => 'required|string|max:20',
            'support_email' => 'required|email|max:100',
            'company_phone' => 'required|string|max:30',
            'company_address' => 'required|string|max:200',
        ]);

        // Actualizar configuraciones generales
        SystemSetting::set('app_name', $request->app_name, 'string', 'general', 'Nombre de la aplicación', true);
        SystemSetting::set('app_version', $request->app_version, 'string', 'general', 'Versión actual de la aplicación móvil', true);
        SystemSetting::set('support_email', $request->support_email, 'string', 'general', 'Email de soporte', true);
        
        // Actualizar configuraciones de contacto
        SystemSetting::set('company_phone', $request->company_phone, 'string', 'contact', 'Teléfono principal de la empresa', true);
        SystemSetting::set('company_address', $request->company_address, 'string', 'contact', 'Dirección de la empresa', true);

        return redirect()->route('admin.settings.general')
            ->with('success', 'Configuraciones generales actualizadas correctamente.');
    }

    /**
     * Limpiar cache de configuraciones
     */
    public function clearCache()
    {
        SystemSetting::clearCache();
        
        return redirect()->back()
            ->with('success', 'Cache de configuraciones limpiado correctamente.');
    }
}
