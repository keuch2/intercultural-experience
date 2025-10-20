# API: Recuperación de Contraseña

## Endpoints Implementados

### 1. Solicitar Link de Recuperación

**POST** `/api/password/forgot`

**Rate Limit:** 3 intentos por hora

**Request:**
```json
{
  "email": "usuario@example.com"
}
```

**Response Success (200):**
```json
{
  "status": "success",
  "message": "Si el email existe en nuestro sistema, recibirás un link de recuperación."
}
```

**Response Error (422):**
```json
{
  "message": "The email field is required.",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

**Response Rate Limit (429):**
```json
{
  "message": "Too Many Attempts."
}
```

---

### 2. Validar Token

**POST** `/api/password/validate-token`

**Request:**
```json
{
  "token": "abc123...xyz789"
}
```

**Response Success (200):**
```json
{
  "status": "success",
  "message": "Token válido",
  "email": "usuario@example.com"
}
```

**Response Error (422):**
```json
{
  "status": "error",
  "message": "Token inválido o expirado"
}
```

---

### 3. Resetear Contraseña

**POST** `/api/password/reset`

**Request:**
```json
{
  "token": "abc123...xyz789",
  "password": "NuevaContraseña123!",
  "password_confirmation": "NuevaContraseña123!"
}
```

**Validaciones de Contraseña:**
- Mínimo 8 caracteres
- Al menos una mayúscula
- Al menos una minúscula
- Al menos un número
- Al menos un carácter especial (@$!%*#?&)

**Response Success (200):**
```json
{
  "status": "success",
  "message": "Contraseña actualizada exitosamente. Por favor, inicia sesión con tu nueva contraseña."
}
```

**Response Error - Token Inválido (422):**
```json
{
  "status": "error",
  "message": "Token inválido o expirado. Por favor, solicita un nuevo link de recuperación."
}
```

**Response Error - Validación (422):**
```json
{
  "message": "The password field must be at least 8 characters.",
  "errors": {
    "password": [
      "La contraseña debe tener al menos 8 caracteres",
      "La contraseña debe contener al menos una mayúscula, una minúscula, un número y un carácter especial (@$!%*#?&)"
    ]
  }
}
```

---

## Flujo Completo

```
1. Usuario: POST /api/password/forgot
   { "email": "user@example.com" }
   
2. Sistema: Genera token y envía email
   
3. Usuario: Recibe email con link
   Link: interculturalexp://reset-password?token=abc123
   
4. App: Valida token
   POST /api/password/validate-token
   { "token": "abc123" }
   
5. App: Muestra formulario de nueva contraseña
   
6. Usuario: Ingresa nueva contraseña
   
7. App: Envía reset
   POST /api/password/reset
   {
     "token": "abc123",
     "password": "NewPass123!",
     "password_confirmation": "NewPass123!"
   }
   
8. Sistema: 
   - Actualiza contraseña
   - Elimina token
   - Cierra todas las sesiones
   
9. Usuario: Inicia sesión con nueva contraseña
```

---

## Seguridad Implementada

✅ **Rate Limiting:** 3 intentos por hora para prevenir spam  
✅ **Token Hasheado:** Los tokens se guardan hasheados en BD  
✅ **Expiración:** Tokens expiran en 60 minutos  
✅ **Un solo token:** Solo un token activo por usuario  
✅ **Validación fuerte:** Contraseñas deben cumplir requisitos  
✅ **Logout automático:** Cierra todas las sesiones al resetear  
✅ **No revelar usuarios:** No indica si el email existe o no  

---

## Email Enviado

**Asunto:** Recuperar Contraseña - Intercultural Experience

**Contenido:**
```
¡Hola [Nombre]!

Recibiste este email porque solicitaste recuperar tu contraseña.

Para restablecer tu contraseña, haz click en el botón de abajo:

[Recuperar Contraseña]

Para la aplicación móvil: Abre este link en tu dispositivo móvil.

Este link expirará en 60 minutos.

Si no solicitaste recuperar tu contraseña, puedes ignorar este email de forma segura.

Saludos,
Intercultural Experience
```

---

## Testing con cURL

### 1. Solicitar reset:
```bash
curl -X POST http://localhost/api/password/forgot \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com"}'
```

### 2. Validar token:
```bash
curl -X POST http://localhost/api/password/validate-token \
  -H "Content-Type: application/json" \
  -d '{"token":"abc123..."}'
```

### 3. Resetear:
```bash
curl -X POST http://localhost/api/password/reset \
  -H "Content-Type: application/json" \
  -d '{
    "token":"abc123...",
    "password":"NewPass123!",
    "password_confirmation":"NewPass123!"
  }'
```

---

## Errores Comunes

### "Too Many Attempts"
- **Causa:** Más de 3 intentos en 1 hora
- **Solución:** Esperar 1 hora o limpiar rate limit en BD

### "Token inválido o expirado"
- **Causa:** Token usado, expirado (>60 min) o incorrecto
- **Solución:** Solicitar nuevo link

### "Las contraseñas no coinciden"
- **Causa:** password !== password_confirmation
- **Solución:** Verificar que ambos campos sean idénticos

### Email no llega
- **Causa:** SMTP mal configurado o email en spam
- **Solución:** Verificar configuración SMTP y carpeta de spam

---

## Próximos Pasos

1. ✅ Backend implementado
2. ⏳ Crear pantallas móvil (React Native)
3. ⏳ Crear vistas admin web
4. ⏳ Crear tests automatizados
