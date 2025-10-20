<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a Intercultural Experience</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .credentials {
            background: white;
            padding: 20px;
            border-left: 4px solid #667eea;
            margin: 20px 0;
            border-radius: 5px;
        }
        .footer {
            text-align: center;
            color: #666;
            font-size: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>¡Bienvenido a Intercultural Experience!</h1>
        <p>Tu viaje comienza aquí</p>
    </div>
    
    <div class="content">
        <h2>Hola {{ $user->name }},</h2>
        
        @if($user->role === 'agent')
            <p>Te damos la bienvenida a Intercultural Experience como <strong>Agente</strong>. Estamos emocionados de tenerte en nuestro equipo.</p>
            
            <p>Como agente, podrás:</p>
            <ul>
                <li>Crear y gestionar participantes</li>
                <li>Asignar programas de intercambio</li>
                <li>Hacer seguimiento de aplicaciones</li>
                <li>Ver estadísticas de tus participantes</li>
            </ul>
        @elseif($user->role === 'admin')
            <p>Te damos la bienvenida a Intercultural Experience como <strong>Administrador</strong>. Tienes acceso completo a todas las funcionalidades del sistema.</p>
        @else
            <p>Te damos la bienvenida a Intercultural Experience. Estamos emocionados de ser parte de tu aventura de intercambio cultural.</p>
            
            <p>A través de nuestra plataforma podrás:</p>
            <ul>
                <li>Explorar programas de intercambio</li>
                <li>Aplicar a programas</li>
                <li>Hacer seguimiento de tu aplicación</li>
                <li>Comunicarte con tu agente</li>
            </ul>
        @endif

        @if($temporaryPassword)
            <div class="credentials">
                <h3>🔐 Tus Credenciales de Acceso</h3>
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Contraseña Temporal:</strong> {{ $temporaryPassword }}</p>
                <p style="color: #d9534f; font-size: 14px;">
                    ⚠️ Por seguridad, te recomendamos cambiar tu contraseña al iniciar sesión por primera vez.
                </p>
            </div>
        @endif

        @if($user->role === 'agent' || $user->role === 'admin')
            <a href="{{ url('/login') }}" class="button">Acceder al Panel</a>
        @else
            <p>Descarga nuestra aplicación móvil para comenzar:</p>
            <a href="#" class="button">Descargar para iOS</a>
            <a href="#" class="button">Descargar para Android</a>
        @endif

        <p style="margin-top: 30px;">
            Si tienes alguna pregunta, no dudes en contactarnos.<br>
            Estamos aquí para ayudarte en cada paso del camino.
        </p>

        <p>
            ¡Bienvenido a la familia Intercultural Experience!<br>
            <strong>Equipo IE</strong>
        </p>
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} Intercultural Experience. Todos los derechos reservados.</p>
        <p>Este es un email automático, por favor no responder.</p>
    </div>
</body>
</html>
