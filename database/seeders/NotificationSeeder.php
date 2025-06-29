<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', 'user')->get();
        
        $notificationCategories = [
            'application_status' => [
                'Tu solicitud ha sido recibida',
                'Tu solicitud está en revisión',
                'Tu solicitud ha sido aprobada',
                'Tu solicitud ha sido rechazada',
                'Se requiere información adicional para tu solicitud',
            ],
            'document_status' => [
                'Tu documento ha sido recibido',
                'Tu documento ha sido verificado',
                'Tu documento ha sido rechazado',
                'Se requiere un nuevo documento',
                'Recordatorio: documento pendiente de envío',
            ],
            'payment_status' => [
                'Pago recibido correctamente',
                'Pago pendiente de verificación',
                'Pago rechazado',
                'Recordatorio de pago pendiente',
                'Factura disponible para descarga',
            ],
            'program_info' => [
                'Información importante sobre tu programa',
                'Cambio en las fechas del programa',
                'Nuevos requisitos para tu programa',
                'Sesión de orientación programada',
                'Información de alojamiento disponible',
            ],
            'system' => [
                'Bienvenido a Intercultural Experience',
                'Tu cuenta ha sido creada exitosamente',
                'Cambio en los términos de servicio',
                'Actualización de la plataforma',
                'Mantenimiento programado del sistema',
            ],
        ];
        
        $notificationMessages = [
            'application_status' => [
                'Hemos recibido tu solicitud para el programa. Revisa los requisitos pendientes en tu perfil.',
                'Tu solicitud está siendo revisada por nuestro equipo. Te notificaremos cuando haya una actualización.',
                '¡Felicidades! Tu solicitud ha sido aprobada. Revisa los siguientes pasos en tu perfil.',
                'Lamentamos informarte que tu solicitud ha sido rechazada. Contacta a soporte para más información.',
                'Necesitamos información adicional para continuar con tu solicitud. Revisa los detalles en tu perfil.',
            ],
            'document_status' => [
                'Hemos recibido tu documento. Será revisado en los próximos días.',
                'Tu documento ha sido verificado y aprobado. No se requiere ninguna acción adicional.',
                'Tu documento ha sido rechazado. Revisa los comentarios y vuelve a enviarlo.',
                'Se requiere un nuevo documento para tu solicitud. Revisa los detalles en tu perfil.',
                'Recuerda que tienes documentos pendientes de envío. La fecha límite es en 7 días.',
            ],
            'payment_status' => [
                'Hemos recibido tu pago correctamente. Gracias por completar este requisito.',
                'Tu pago está siendo verificado por nuestro departamento financiero.',
                'Tu pago ha sido rechazado. Por favor, contacta a tu entidad bancaria o intenta con otro método de pago.',
                'Recuerda que tienes un pago pendiente. La fecha límite es en 5 días.',
                'Tu factura está disponible para descarga en la sección de pagos de tu perfil.',
            ],
            'program_info' => [
                'Hay información importante sobre tu programa. Revisa los detalles en la sección de programas.',
                'Ha habido un cambio en las fechas de tu programa. Revisa la nueva programación en tu perfil.',
                'Se han añadido nuevos requisitos para tu programa. Revisa la sección de requisitos.',
                'Se ha programado una sesión de orientación obligatoria. Fecha: 15/06/2025, 18:00 hrs.',
                'La información sobre tu alojamiento ya está disponible. Revisa los detalles en tu perfil.',
            ],
            'system' => [
                '¡Bienvenido a Intercultural Experience! Estamos emocionados de tenerte con nosotros.',
                'Tu cuenta ha sido creada exitosamente. Completa tu perfil para comenzar.',
                'Hemos actualizado nuestros términos de servicio. Por favor, revísalos en la sección de términos y condiciones.',
                'Hemos realizado mejoras en nuestra plataforma. Disfruta de la nueva experiencia.',
                'Habrá un mantenimiento programado el día 20/05/2025 de 02:00 a 04:00 hrs.',
            ],
        ];
        
        // Crear notificaciones para cada usuario
        foreach ($users as $user) {
            // Cada usuario tendrá entre 5 y 10 notificaciones
            $numNotifications = rand(5, 10);
            
            for ($i = 0; $i < $numNotifications; $i++) {
                $category = array_rand($notificationCategories);
                $messageIndex = array_rand($notificationCategories[$category]);
                
                $createdAt = now()->subDays(rand(1, 30));
                $isRead = rand(0, 100) < 70;
                
                Notification::create([
                    'user_id' => $user->id,
                    'title' => $notificationCategories[$category][$messageIndex],
                    'message' => $notificationMessages[$category][$messageIndex],
                    'category' => $category,
                    'is_read' => $isRead,
                    'created_at' => $createdAt,
                ]);
            }
        }
        
        // Crear notificaciones relacionadas con aplicaciones específicas
        $applications = Application::all();
        
        foreach ($applications as $application) {
            $user = User::find($application->user_id);
            $program = $application->program;
            
            // Notificación de recepción de solicitud
            Notification::create([
                'user_id' => $user->id,
                'title' => 'Solicitud recibida para ' . $program->name,
                'message' => 'Hemos recibido tu solicitud para el programa ' . $program->name . '. Revisa los requisitos pendientes en tu perfil.',
                'category' => 'application_status',
                'is_read' => true,
                'created_at' => $application->applied_at,
            ]);
            
            // Notificaciones adicionales según el estado de la aplicación
            if ($application->status !== 'pending') {
                $statusDate = $application->completed_at ?? now()->subDays(rand(5, 15));
                $isRead = rand(0, 100) < 80;
                
                $title = '';
                $message = '';
                
                if ($application->status === 'in_review') {
                    $title = 'Solicitud en revisión para ' . $program->name;
                    $message = 'Tu solicitud para el programa ' . $program->name . ' está siendo revisada por nuestro equipo. Te notificaremos cuando haya una actualización.';
                } elseif ($application->status === 'approved') {
                    $title = '¡Solicitud aprobada para ' . $program->name . '!';
                    $message = '¡Felicidades! Tu solicitud para el programa ' . $program->name . ' ha sido aprobada. Revisa los siguientes pasos en tu perfil.';
                } elseif ($application->status === 'rejected') {
                    $title = 'Solicitud rechazada para ' . $program->name;
                    $message = 'Lamentamos informarte que tu solicitud para el programa ' . $program->name . ' ha sido rechazada. Contacta a soporte para más información.';
                }
                
                Notification::create([
                    'user_id' => $user->id,
                    'title' => $title,
                    'message' => $message,
                    'category' => 'application_status',
                    'is_read' => $isRead,
                    'created_at' => $statusDate,
                ]);
            }
        }
    }
}
