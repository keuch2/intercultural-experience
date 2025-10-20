<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Has sido asignado a un programa!</title>
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
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
        .program-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        .program-title {
            font-size: 24px;
            color: #28a745;
            margin-bottom: 15px;
        }
        .program-detail {
            margin: 10px 0;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .program-detail:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #666;
            display: inline-block;
            min-width: 120px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .next-steps {
            background: #e7f3ff;
            padding: 20px;
            border-left: 4px solid #0056b3;
            border-radius: 5px;
            margin: 20px 0;
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
        <h1>🎉 ¡Felicidades {{ $user->name }}!</h1>
        <p>Has sido asignado a un programa</p>
    </div>
    
    <div class="content">
        <p style="font-size: 18px;">Nos complace informarte que has sido asignado al siguiente programa:</p>

        <div class="program-card">
            <div class="program-title">{{ $program->name }}</div>
            
            <div class="program-detail">
                <span class="label">📍 Ubicación:</span>
                <span>{{ $program->location }}</span>
            </div>

            @if($program->duration)
            <div class="program-detail">
                <span class="label">⏱️ Duración:</span>
                <span>{{ $program->duration }}</span>
            </div>
            @endif

            @if($program->start_date)
            <div class="program-detail">
                <span class="label">📅 Fecha de Inicio:</span>
                <span>{{ \Carbon\Carbon::parse($program->start_date)->format('d/m/Y') }}</span>
            </div>
            @endif

            @if($program->cost)
            <div class="program-detail">
                <span class="label">💰 Costo:</span>
                <span>${{ number_format($program->cost, 2) }} {{ $program->currency ?? 'USD' }}</span>
            </div>
            @endif

            <div class="program-detail">
                <span class="label">👤 Asignado por:</span>
                <span>{{ $assignedBy->name }}</span>
            </div>

            @if($assignment->notes)
            <div class="program-detail">
                <span class="label">📝 Notas:</span>
                <div style="margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 3px;">
                    {{ $assignment->notes }}
                </div>
            </div>
            @endif
        </div>

        <div class="next-steps">
            <h3 style="margin-top: 0;">📋 Próximos Pasos:</h3>
            <ol>
                <li><strong>Revisa los detalles del programa</strong> en tu aplicación móvil</li>
                <li><strong>Completa los requisitos</strong> necesarios para tu aplicación</li>
                <li><strong>Mantén contacto</strong> con tu agente {{ $assignedBy->name }}</li>
                <li><strong>Prepara tu documentación</strong> según los requisitos del programa</li>
            </ol>
        </div>

        <p style="text-align: center;">
            <a href="#" class="button">Ver Programa en la App</a>
        </p>

        <h3>¿Tienes Preguntas?</h3>
        <p>Tu agente {{ $assignedBy->name }} está disponible para ayudarte:</p>
        <ul>
            <li>📧 Email: {{ $assignedBy->email }}</li>
            @if($assignedBy->phone)
                <li>📞 Teléfono: {{ $assignedBy->phone }}</li>
            @endif
        </ul>

        <p style="margin-top: 30px; padding: 15px; background: #d4edda; border-radius: 5px; color: #155724;">
            <strong>¡Este es un paso importante en tu viaje!</strong><br>
            Estamos emocionados de acompañarte en esta experiencia única.
        </p>

        <p>
            Saludos cordiales,<br>
            <strong>Equipo Intercultural Experience</strong>
        </p>
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} Intercultural Experience. Todos los derechos reservados.</p>
        <p>Este es un email automático, por favor no responder.</p>
    </div>
</body>
</html>
