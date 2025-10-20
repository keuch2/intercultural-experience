<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Mail\WelcomeUser;
use App\Mail\CredentialsSent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Comando para probar el envío de emails
 * Uso: php artisan emails:test {email} {--type=welcome}
 */
class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:test 
                            {email : Email de destino} 
                            {--type=welcome : Tipo de email (welcome, credentials)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía un email de prueba del sistema de notificaciones';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $type = $this->option('type');

        // Verificar configuración de email
        if (!config('mail.mailers.smtp.host')) {
            $this->error('❌ La configuración de SMTP no está completa.');
            $this->info('Configura las variables en el archivo .env:');
            $this->line('MAIL_MAILER=smtp');
            $this->line('MAIL_HOST=smtp.gmail.com');
            $this->line('MAIL_PORT=587');
            $this->line('MAIL_USERNAME=tu-email@gmail.com');
            $this->line('MAIL_PASSWORD=tu-app-password');
            $this->line('MAIL_ENCRYPTION=tls');
            $this->line('MAIL_FROM_ADDRESS=noreply@interculturalexperience.com');
            $this->line('MAIL_FROM_NAME="Intercultural Experience"');
            return 1;
        }

        $this->info("📧 Enviando email de prueba a: {$email}");

        try {
            // Crear usuario de prueba
            $testUser = new User([
                'name' => 'Usuario de Prueba',
                'email' => $email,
                'role' => 'user',
                'phone' => '+595 123 456789',
                'country' => 'Paraguay',
            ]);

            switch ($type) {
                case 'welcome':
                    Mail::to($email)->send(new WelcomeUser($testUser));
                    $this->info('✅ Email de bienvenida enviado correctamente');
                    break;

                case 'credentials':
                    $tempPassword = 'TestPassword123!';
                    Mail::to($email)->send(new CredentialsSent($testUser, $tempPassword));
                    $this->info('✅ Email con credenciales enviado correctamente');
                    $this->line("Contraseña de prueba: {$tempPassword}");
                    break;

                default:
                    $this->error("❌ Tipo de email no válido: {$type}");
                    $this->info('Tipos disponibles: welcome, credentials');
                    return 1;
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error al enviar email: ' . $e->getMessage());
            $this->line('');
            $this->error('Stack trace:');
            $this->line($e->getTraceAsString());
            return 1;
        }
    }
}
