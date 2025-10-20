<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tus Credenciales de Acceso</title>
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
        .credentials-box {
            background: white;
            padding: 25px;
            border-left: 4px solid #28a745;
            margin: 25px 0;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .credential-item {
            margin: 15px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 3px;
        }
        .credential-label {
            font-weight: bold;
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
        }
        .credential-value {
            font-size: 16px;
            color: #333;
            font-family: 'Courier New', monospace;
            margin-top: 5px;
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
        .alert {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
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
        <h1>🔐 Tus Credenciales de Acceso</h1>
        <p>Intercultural Experience</p>
    </div>
    
    <div class="content">
        <h2>Hola {{ $user->name }},</h2>
        
        @if($createdBy)
            <p>Tu cuenta ha sido creada por <strong>{{ $createdBy->name }}</strong>.</p>
        @else
            <p>Tu cuenta ha sido creada exitosamente en Intercultural Experience.</p>
        @endif

        <p>A continuación encontrarás tus credenciales de acceso:</p>

        <div class="credentials-box">
            <div class="credential-item">
                <div class="credential-label">Email / Usuario</div>
                <div class="credential-value">{{ $user->email }}</div>
            </div>

            <div class="credential-item">
                <div class="credential-label">Contraseña Temporal</div>
                <div class="credential-value">{{ $temporaryPassword }}</div>
            </div>
        </div>

        <div class="alert">
            <strong>⚠️ Importante:</strong>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li>Esta es una contraseña temporal generada automáticamente</li>
                <li>Por seguridad, te recomendamos cambiarla al iniciar sesión</li>
                <li>No compartas esta información con nadie</li>
                <li>Guarda esta contraseña en un lugar seguro</li>
            </ul>
        </div>

        @if($user->role === 'agent' || $user->role === 'admin')
            <h3>Acceso al Panel</h3>
            <p>Puedes acceder al panel de {{ $user->role === 'agent' ? 'agentes' : 'administración' }} usando el siguiente enlace:</p>
            <a href="{{ url('/login') }}" class="button">Iniciar Sesión</a>
        @else
            <h3>Acceso a la Aplicación Móvil</h3>
            <p>Descarga nuestra aplicación móvil para comenzar tu experiencia:</p>
            <a href="#" class="button">Descargar para iOS</a>
            <a href="#" class="button">Descargar para Android</a>
        @endif

        <h3>¿Necesitas Ayuda?</h3>
        <p>Si tienes problemas para acceder o alguna pregunta:</p>
        <ul>
            @if($createdBy)
                <li>Contacta a tu agente: {{ $createdBy->email }}</li>
            @endif
            <li>Escríbenos a: soporte@interculturalexperience.com</li>
            <li>Llámanos al: +1 (800) 123-4567</li>
        </ul>

        <p style="margin-top: 30px;">
            ¡Bienvenido a la familia Intercultural Experience!<br>
            <strong>Equipo IE</strong>
        </p>
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} Intercultural Experience. Todos los derechos reservados.</p>
        <p>Este es un email automático, por favor no responder.</p>
        <p style="margin-top: 10px;">
            <small>Si no solicitaste esta cuenta, por favor ignora este email.</small>
        </p>
    </div>
</body>
</html>
