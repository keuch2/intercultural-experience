<?php

namespace Database\Seeders;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Seeder;

class SupportTicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', 'user')->get();
        $statuses = ['open', 'in_progress', 'closed'];
        
        // Agrupar temas por categorías para organización interna (no se guardan en la BD)
        $subjects = [
            // Temas técnicos
            'No puedo acceder a mi cuenta',
            'Error al subir documentos',
            'La aplicación móvil no funciona',
            'Problema con el formulario de registro',
            'No recibo correos de confirmación',
            // Temas de pagos
            'Problema con el pago de inscripción',
            'No se registró mi pago',
            'Necesito factura de mi pago',
            'Devolución de pago',
            'Opciones de pago alternativas',
            // Temas de documentos
            'Documento rechazado sin explicación',
            'Necesito más tiempo para entregar documentos',
            'Problema con formato de documentos',
            'Documentos pendientes de verificación',
            'Requisitos de documentación poco claros',
            // Temas de programas
            'Información sobre fechas del programa',
            'Cambio de programa',
            'Detalles sobre alojamiento',
            'Consulta sobre requisitos del programa',
            'Información sobre becas disponibles',
            // Temas de visa
            'Requisitos de visa',
            'Carta de aceptación para visa',
            'Tiempo de procesamiento de visa',
            'Documentos adicionales para visa',
            'Problema con la embajada',
            // Otros temas
            'Consulta general sobre Intercultural Experience',
            'Necesito contactar a un administrador',
            'Problema no listado',
            'Sugerencia para el programa',
            'Consulta sobre seguro médico',
        ];
        
        $messages = [
            // Mensajes técnicos
            'Cuando intento acceder a mi cuenta, me aparece un error 404.',
            'Al intentar subir mi pasaporte en formato PDF, el sistema muestra un error de timeout.',
            'La aplicación móvil se cierra inesperadamente cuando intento ver mis requisitos pendientes.',
            'No puedo completar el formulario de registro porque el botón de enviar no responde.',
            'He solicitado recuperar mi contraseña varias veces pero no recibo ningún correo.',
            // Mensajes de pagos
            'Realicé el pago hace 3 días pero aún aparece como pendiente en mi perfil.',
            'Necesito una factura oficial para el pago que realicé el 15 de abril.',
            'El sistema no acepta mi tarjeta de crédito, ¿hay otras formas de pago disponibles?',
            'Pagué dos veces por error y necesito solicitar la devolución de uno de los pagos.',
            'El monto que me cobran no coincide con el que se indica en la información del programa.',
            // Mensajes de documentos
            'Mi certificado de idioma fue rechazado sin explicación, ¿podrían indicarme el motivo?',
            'Necesito una prórroga para entregar mi carta de motivación debido a problemas personales.',
            'No puedo subir mi pasaporte porque excede el tamaño máximo permitido.',
            'Subí todos mis documentos hace una semana pero siguen apareciendo como pendientes de revisión.',
            'No entiendo qué tipo de seguro médico necesito para el programa en Alemania.',
            // Mensajes de programas
            '¿Cuándo comienzan las clases del programa en España? No encuentro esta información.',
            'Me gustaría cambiar mi solicitud del programa de Francia al de Alemania, ¿es posible?',
            '¿El alojamiento está incluido en el costo del programa o debo buscarlo por mi cuenta?',
            '¿Cuál es el nivel mínimo de inglés requerido para el programa en Canadá?',
            '¿Existen becas disponibles para el programa en Japón?',
            // Mensajes de visa
            'Necesito una carta de aceptación para solicitar mi visa de estudiante.',
            '¿Cuánto tiempo tarda normalmente el proceso de visa para Australia?',
            'La embajada me pide documentos adicionales que no están en su lista de requisitos.',
            '¿Ofrecen algún tipo de asesoría para el proceso de visa?',
            'Mi visa fue rechazada, ¿puedo aplazar mi participación en el programa?',
            // Otros mensajes
            'Me gustaría saber si ofrecen programas en América Latina.',
            'Necesito hablar con un administrador sobre una situación especial.',
            'Tengo una sugerencia para mejorar el proceso de aplicación.',
            '¿El seguro médico debe contratarse antes de viajar o puede hacerse al llegar al país?',
            '¿Cuál es la política de cancelación del programa?',
        ];
        
        // Crear tickets de soporte para cada usuario
        foreach ($users as $user) {
            // Cada usuario tendrá entre 1 y 3 tickets
            $numTickets = rand(1, 3);
            
            for ($i = 0; $i < $numTickets; $i++) {
                $messageIndex = array_rand($messages);
                $status = $statuses[array_rand($statuses)];
                
                $createdAt = now()->subDays(rand(1, 30));
                $updatedAt = (clone $createdAt)->addDays(rand(0, 5));
                
                if ($status === 'closed') {
                    $updatedAt = (clone $createdAt)->addDays(rand(1, 7));
                }
                
                SupportTicket::create([
                    'user_id' => $user->id,
                    'subject' => $subjects[$messageIndex],
                    'message' => $messages[$messageIndex],
                    'status' => $status,
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ]);
            }
        }
        
        // Crear algunos tickets adicionales para tener más variedad
        for ($i = 0; $i < 10; $i++) {
            $user = $users->random();
            $messageIndex = array_rand($messages);
            $status = $statuses[array_rand($statuses)];
            
            $createdAt = now()->subDays(rand(1, 45));
            $updatedAt = (clone $createdAt)->addDays(rand(0, 7));
            
            if ($status === 'closed') {
                $updatedAt = (clone $createdAt)->addDays(rand(1, 10));
            }
            
            SupportTicket::create([
                'user_id' => $user->id,
                'subject' => $subjects[$messageIndex],
                'message' => $messages[$messageIndex],
                'status' => $status,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]);
        }
    }
}
