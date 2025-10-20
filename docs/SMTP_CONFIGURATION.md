# Configuración de SMTP para Recuperación de Contraseña

## Configuración en .env

Actualiza tu archivo `.env` con las siguientes variables:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@interculturalexperience.com"
MAIL_FROM_NAME="Intercultural Experience"
```

## Opciones de Proveedores SMTP

### 1. Gmail (Recomendado para desarrollo)

**Configuración:**
```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

**Pasos:**
1. Ir a [Google Account Security](https://myaccount.google.com/security)
2. Activar "2-Step Verification"
3. Ir a "App passwords"
4. Generar contraseña de aplicación para "Mail"
5. Usar esa contraseña en `MAIL_PASSWORD`

### 2. Mailgun (Recomendado para producción)

**Configuración:**
```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your-domain.mailgun.org
MAILGUN_SECRET=your-mailgun-api-key
MAILGUN_ENDPOINT=api.mailgun.net
```

**Pasos:**
1. Crear cuenta en [Mailgun](https://www.mailgun.com/)
2. Verificar dominio
3. Obtener API key
4. Instalar: `composer require symfony/mailgun-mailer symfony/http-client`

### 3. SendGrid

**Configuración:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
```

### 4. Amazon SES

**Configuración:**
```env
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
```

### 5. Mailtrap (Solo para testing)

**Configuración:**
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
```

## Probar Configuración

### Desde Tinker:
```bash
php artisan tinker
```

```php
Mail::raw('Test email', function($message) {
    $message->to('test@example.com')->subject('Test');
});
```

### Comando de prueba:
```bash
php artisan make:command TestEmail
```

## Troubleshooting

### Error: "Connection could not be established"
- Verificar firewall
- Verificar credenciales
- Verificar puerto (587 o 465)

### Error: "Authentication failed"
- Verificar contraseña de aplicación (Gmail)
- Verificar API key (otros servicios)

### Emails no llegan:
- Revisar carpeta de spam
- Verificar `MAIL_FROM_ADDRESS` es válido
- Verificar límites de envío del proveedor

## Configuración de Queue (Opcional pero Recomendado)

Para enviar emails de forma asíncrona:

```env
QUEUE_CONNECTION=database
```

```bash
php artisan queue:table
php artisan migrate
php artisan queue:work
```

## Seguridad

⚠️ **NUNCA** commitear credenciales reales en el repositorio
✅ Usar variables de entorno
✅ Usar servicios con API keys rotables
✅ Configurar rate limiting (ya implementado: 3 intentos/hora)

## Testing

Los tests están en:
- `tests/Feature/PasswordResetTest.php`

Ejecutar:
```bash
php artisan test --filter PasswordResetTest
```
