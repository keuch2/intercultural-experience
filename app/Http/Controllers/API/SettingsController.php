<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SystemSetting;

class SettingsController extends Controller
{
    /**
     * Obtiene todas las configuraciones públicas
     */
    public function index()
    {
        try {
            $settings = SystemSetting::getPublicSettings();
            
            return response()->json([
                'success' => true,
                'data' => $settings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener configuraciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene configuraciones específicas de WhatsApp
     */
    public function whatsapp()
    {
        try {
            $whatsappSettings = SystemSetting::getGroup('whatsapp');
            
            // Solo devolver configuraciones públicas de WhatsApp
            $publicSettings = [
                'whatsapp_support_number' => $whatsappSettings['whatsapp_support_number'] ?? null,
                'whatsapp_support_enabled' => $whatsappSettings['whatsapp_support_enabled'] ?? false,
            ];
            
            return response()->json([
                'success' => true,
                'data' => $publicSettings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener configuraciones de WhatsApp',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene configuraciones de contacto
     */
    public function contact()
    {
        try {
            $generalSettings = SystemSetting::getGroup('general');
            $contactSettings = SystemSetting::getGroup('contact');
            
            $contactInfo = [
                'app_name' => $generalSettings['app_name'] ?? 'Intercultural Experience',
                'support_email' => $generalSettings['support_email'] ?? null,
                'company_phone' => $contactSettings['company_phone'] ?? null,
                'company_address' => $contactSettings['company_address'] ?? null,
                'whatsapp_number' => SystemSetting::get('whatsapp_support_number'),
                'whatsapp_enabled' => SystemSetting::get('whatsapp_support_enabled', false),
            ];
            
            return response()->json([
                'success' => true,
                'data' => $contactInfo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información de contacto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene información de la aplicación
     */
    public function appInfo()
    {
        try {
            $appInfo = [
                'app_name' => SystemSetting::get('app_name', 'Intercultural Experience'),
                'app_version' => SystemSetting::get('app_version', '1.0.0'),
                'support_email' => SystemSetting::get('support_email'),
                'whatsapp_support_enabled' => SystemSetting::get('whatsapp_support_enabled', false),
            ];
            
            return response()->json([
                'success' => true,
                'data' => $appInfo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información de la aplicación',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
