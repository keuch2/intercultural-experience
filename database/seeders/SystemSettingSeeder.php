<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Configuraciones de WhatsApp
        SystemSetting::set(
            'whatsapp_support_number',
            '+595981234567',
            'string',
            'whatsapp',
            'Número de WhatsApp para soporte al cliente',
            true // Es público para que la app móvil pueda accederlo
        );

        SystemSetting::set(
            'whatsapp_support_enabled',
            true,
            'boolean',
            'whatsapp',
            'Habilitar/deshabilitar soporte por WhatsApp',
            true
        );

        SystemSetting::set(
            'whatsapp_welcome_message',
            'Hola! Soy del equipo de soporte de Intercultural Experience. ¿En qué puedo ayudarte?',
            'string',
            'whatsapp',
            'Mensaje de bienvenida para WhatsApp',
            false
        );

        // Configuraciones generales
        SystemSetting::set(
            'app_name',
            'Intercultural Experience',
            'string',
            'general',
            'Nombre de la aplicación',
            true
        );

        SystemSetting::set(
            'app_version',
            '1.0.0',
            'string',
            'general',
            'Versión actual de la aplicación móvil',
            true
        );

        SystemSetting::set(
            'support_email',
            'soporte@interculturalexperience.com',
            'string',
            'general',
            'Email de soporte',
            true
        );

        // Configuraciones de contacto
        SystemSetting::set(
            'company_phone',
            '+595 21 123 4567',
            'string',
            'contact',
            'Teléfono principal de la empresa',
            true
        );

        SystemSetting::set(
            'company_address',
            'Asunción, Paraguay',
            'string',
            'contact',
            'Dirección de la empresa',
            true
        );

        // Configuraciones de notificaciones
        SystemSetting::set(
            'push_notifications_enabled',
            true,
            'boolean',
            'notifications',
            'Habilitar notificaciones push',
            false
        );

        SystemSetting::set(
            'email_notifications_enabled',
            true,
            'boolean',
            'notifications',
            'Habilitar notificaciones por email',
            false
        );
    }
}
