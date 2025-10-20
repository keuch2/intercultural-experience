# 🌍 Intercultural Experience Platform - Windsurf Rules & Guidelines

## 📋 Índice

1. [Estructura del Proyecto](#1-estructura-del-proyecto)
2. [Convenciones de Nomenclatura](#2-convenciones-de-nomenclatura)  
3. [Estándares de Documentación](#3-estándares-de-documentación)
4. [Buenas Prácticas de Seguridad](#4-buenas-prácticas-de-seguridad)
5. [Estándares de Testing](#5-estándares-de-testing)
6. [Desarrollo Colaborativo](#6-desarrollo-colaborativo)
7. [Criterios de Despliegue](#7-criterios-de-despliegue)
8. [Arquitectura de Backend](#8-arquitectura-de-backend)
9. [Arquitectura de Frontend](#9-arquitectura-de-frontend)
10. [Base de Datos y Migraciones](#10-base-de-datos-y-migraciones)

---

## 1. Estructura del Proyecto

### 1.1 Organización de Carpetas Backend (Laravel)

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── API/              # Controladores para app móvil
│   │   ├── Admin/            # Controladores panel admin
│   │   └── Auth/             # Controladores autenticación
│   ├── Middleware/           # Middleware personalizado
│   └── Requests/            # Form Request classes
├── Models/                  # Modelos Eloquent
├── Providers/              # Service Providers
└── Rules/                  # Reglas de validación custom
```

**REGLAS:**
- **R1.1**: Separar controladores por audiencia: API (app móvil) vs Admin (panel web)
- **R1.2**: Usar namespaces descriptivos: `App\Http\Controllers\API\`
- **R1.3**: Agrupar middleware relacionado en `app/Http/Middleware/`
- **R1.4**: Form Requests obligatorios para validación compleja

### 1.2 Organización Frontend React Native

```
ieapp/
├── src/
│   ├── components/          # Componentes reutilizables
│   ├── contexts/            # Context providers (Auth, Network)
│   ├── navigation/          # Navegación de la app
│   ├── screens/             # Pantallas principales
│   └── services/            # Servicios API y utilidades
├── assets/                  # Imágenes, iconos
└── app.json                # Configuración Expo
```

**REGLAS:**
- **R1.5**: Estructura basada en funcionalidad, no por tipo de archivo
- **R1.6**: Contexts para estado global (AuthContext, NetworkContext)
- **R1.7**: Servicios API centralizados en `src/services/`
- **R1.8**: Uso de TypeScript estricto (`"strict": true`)

---

## 2. Convenciones de Nomenclatura

### 2.1 Clases y Modelos

**REGLAS:**
- **R2.1**: Modelos en PascalCase singular: `User`, `Program`, `Application`
- **R2.2**: Controladores con sufijo `Controller`: `AuthController`, `ProgramController`
- **R2.3**: Middleware con sufijo `Middleware`: `AdminMiddleware`
- **R2.4**: Form Requests con sufijo `Request`: `RegisterRequest`, `UpdateProfileRequest`

### 2.2 Métodos y Variables

**REGLAS:**
- **R2.5**: Métodos en camelCase: `getUserRequisites()`, `completeRequisite()`
- **R2.6**: Variables en camelCase: `$totalPoints`, `$pendingApplications`
- **R2.7**: Constantes en UPPER_SNAKE_CASE: `STATUS_PENDING`, `ROLE_ADMIN`
- **R2.8**: Scopes de modelo descriptivos: `scopeActive()`, `scopeIe()`, `scopeYfu()`

### 2.3 Rutas y Endpoints

**REGLAS:**
- **R2.9**: Rutas API con prefijo `/api/`: `/api/programs`, `/api/applications`
- **R2.10**: Rutas admin con prefijo `/admin/`: `/admin/programs`, `/admin/users`
- **R2.11**: Separación por categoría: `/admin/ie-programs`, `/admin/yfu-programs`
- **R2.12**: Verbos HTTP correctos: GET (lectura), POST (crear), PUT (actualizar), DELETE (eliminar)

### 2.4 Commits y Ramas

**REGLAS:**
- **R2.13**: Formato commit: `tipo(ámbito): descripción`
  - `feat(auth): add rate limiting to login endpoint`
  - `fix(programs): resolve IE/YFU category filtering`
  - `docs(readme): update installation instructions`
- **R2.14**: Ramas con formato: `feature/nombre`, `bugfix/nombre`, `hotfix/nombre`
- **R2.15**: Ramas principales: `main` (producción), `develop` (desarrollo)

---

## 3. Estándares de Documentación

### 3.1 Comentarios en Código

**REGLAS:**
- **R3.1**: PHPDoc obligatorio en métodos públicos:
```php
/**
 * Calcula el progreso de la solicitud en porcentaje
 * 
 * @return int
 */
public function getProgressPercentage()
```

- **R3.2**: Comentarios explicativos para lógica compleja:
```php
// Super admin siempre tiene todos los permisos
if ($this->role === 'admin' && !$this->role_id) {
    return true;
}
```

### 3.2 Documentación de API

**REGLAS:**
- **R3.3**: Endpoints documentados con estructura de respuesta:
```php
return response()->json([
    'status' => 'success',
    'message' => 'Usuario registrado exitosamente',
    'user' => $user,
    'token' => $token,
], 201);
```

- **R3.4**: Códigos de estado HTTP consistentes:
  - 200: Éxito
  - 201: Creado
  - 401: No autorizado
  - 403: Prohibido
  - 422: Error de validación
  - 500: Error interno

### 3.3 README y Documentación

**REGLAS:**
- **R3.5**: README debe incluir:
  - Descripción del proyecto
  - Requisitos de instalación
  - Instrucciones paso a paso
  - Ejemplos de uso
  - Arquitectura del sistema
  - Información de contacto

---

## 4. Buenas Prácticas de Seguridad

### 4.1 Autenticación y Autorización

**REGLAS:**
- **R4.1**: Laravel Sanctum para API tokens
- **R4.2**: Middleware obligatorio para rutas protegidas:
```php
Route::middleware('auth:sanctum')->group(function () {
    // Rutas protegidas
});
```

- **R4.3**: Separación de roles: admin vs user
```php
if ($user->role === 'admin') {
    return response()->json(['status' => 'error'], 403);
}
```

### 4.2 Manejo de Datos Sensibles

**REGLAS:**
- **R4.4**: Cifrado obligatorio para datos bancarios:
```php
public function setBankInfoAttribute($value)
{
    $this->attributes['bank_info'] = encrypt($value);
}
```

- **R4.5**: Variables de entorno para credenciales:
```env
DB_PASSWORD=your_password
MAIL_PASSWORD=your_mail_password
```

- **R4.6**: Hidden attributes en modelos:
```php
protected $hidden = ['password', 'remember_token'];
```

### 4.3 Rate Limiting

**REGLAS:**
- **R4.7**: Rate limiting en endpoints críticos:
```php
Route::middleware(['throttle:5,1'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});
```

- **R4.8**: Diferentes límites por tipo de operación:
  - Autenticación: 5 intentos/minuto
  - Actualización perfil: 10 intentos/minuto  
  - Envío formularios: 3 intentos/minuto

---

## 5. Estándares de Testing

### 5.1 Estructura de Tests

**REGLAS:**
- **R5.1**: Feature tests para funcionalidad end-to-end
- **R5.2**: Unit tests para lógica de modelos y servicios
- **R5.3**: Usar RefreshDatabase en tests:
```php
use RefreshDatabase;
```

### 5.2 Convenciones de Testing

**REGLAS:**
- **R5.4**: Nombres descriptivos:
```php
public function test_user_can_register_with_strong_password()
public function test_admin_cannot_login_through_mobile_api()
```

- **R5.5**: Assertions específicas:
```php
$response->assertStatus(201)
         ->assertJsonStructure([
             'status', 'message', 'user', 'token'
         ]);
```

### 5.3 Cobertura de Testing

**REGLAS:**
- **R5.6**: Tests obligatorios para:
  - Autenticación y autorización
  - Validación de formularios
  - Rate limiting
  - Funcionalidad crítica de negocio

---

## 6. Desarrollo Colaborativo

### 6.1 Pull Requests

**REGLAS:**
- **R6.1**: PR template obligatorio:
  - Descripción de cambios
  - Tests añadidos/modificados
  - Screenshots para UI
  - Checklist de verificación

- **R6.2**: Revisión de código obligatoria antes de merge
- **R6.3**: Tests deben pasar antes de merge
- **R6.4**: Squash commits al hacer merge

### 6.2 Revisiones de Código

**REGLAS:**
- **R6.5**: Verificar:
  - Cumplimiento de convenciones de nomenclatura
  - Seguridad (autenticación, validación)
  - Performance (queries N+1, caching)
  - Testing adecuado
  - Documentación actualizada

### 6.3 Ramas y Workflow

**REGLAS:**
- **R6.6**: Git Flow:
  - `main`: Código de producción
  - `develop`: Integración de features
  - `feature/*`: Nuevas funcionalidades
  - `bugfix/*`: Corrección de bugs
  - `hotfix/*`: Fixes críticos en producción

---

## 7. Criterios de Despliegue

### 7.1 Prerequisitos de Despliegue

**REGLAS:**
- **R7.1**: Checklist pre-despliegue:
  - [ ] Tests pasando
  - [ ] Migraciones probadas en staging
  - [ ] Variables de entorno configuradas
  - [ ] Backup de base de datos realizado

### 7.2 Proceso de Despliegue

**REGLAS:**
- **R7.2**: Usar script `deploy.sh` para consistencia
- **R7.3**: Modo de mantenimiento durante despliegue:
```bash
php artisan down
# Deploy process
php artisan up
```

- **R7.4**: Verificación post-despliegue:
  - Conectividad de base de datos
  - Endpoints críticos funcionando
  - App móvil conectando correctamente

### 7.3 Rollback

**REGLAS:**
- **R7.5**: Plan de rollback documentado
- **R7.6**: Backup automático antes de migraciones:
```bash
mysqldump database > backup_$(date +%Y%m%d_%H%M%S).sql
```

---

## 8. Arquitectura de Backend

### 8.1 Controladores

**REGLAS:**
- **R8.1**: Separación por audiencia:
  - `API\*Controller`: App móvil (JSON responses)
  - `Admin\*Controller`: Panel admin (Views + JSON)

- **R8.2**: Manejo de errores consistente:
```php
try {
    // Logic here
} catch (\Exception $e) {
    \Log::error('Error context', [
        'error' => $e->getMessage(),
        'user_id' => auth()->id()
    ]);
    
    return response()->json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
    ], 500);
}
```

### 8.2 Modelos y Relaciones

**REGLAS:**
- **R8.3**: Relationships definidas correctamente:
```php
public function applications()
{
    return $this->hasMany(Application::class);
}
```

- **R8.4**: Accessors para datos calculados:
```php
public function getFormattedCostAttribute()
{
    return $this->currency->symbol . ' ' . number_format($this->cost, 2);
}
```

- **R8.5**: Scopes para filtros comunes:
```php
public function scopeActive($query)
{
    return $query->where('is_active', true);
}
```

### 8.3 API Design

**REGLAS:**
- **R8.6**: RESTful conventions:
  - GET `/api/programs` - Lista programas
  - GET `/api/programs/{id}` - Un programa específico
  - POST `/api/programs` - Crear programa
  - PUT `/api/programs/{id}` - Actualizar programa
  - DELETE `/api/programs/{id}` - Eliminar programa

- **R8.7**: Paginación para listas grandes:
```php
return ProgramResource::collection(
    Program::active()->paginate(15)
);
```

---

## 9. Arquitectura de Frontend

### 9.1 Componentes React Native

**REGLAS:**
- **R9.1**: Functional components con hooks
- **R9.2**: TypeScript para type safety
- **R9.3**: Contexts para estado global:
```typescript
export const AuthProvider: React.FC<{children: React.ReactNode}> = ({children}) => {
  // Auth logic
};
```

### 9.2 Navegación

**REGLAS:**
- **R9.4**: React Navigation para navegación
- **R9.5**: Estructura jerárquica clara:
```typescript
<NavigationContainer>
  <AuthProvider>
    <AppNavigator />
  </AuthProvider>
</NavigationContainer>
```

### 9.3 Manejo de Estado

**REGLAS:**
- **R9.6**: Contexts para:
  - AuthContext (usuario autenticado)
  - NetworkContext (estado de conectividad)
- **R9.7**: AsyncStorage para persistencia local
- **R9.8**: Error boundaries para manejo de errores

---

## 10. Base de Datos y Migraciones

### 10.1 Convenciones de Migraciones

**REGLAS:**
- **R10.1**: Nombres descriptivos con timestamp:
```
2025_08_23_182406_add_main_category_and_subcategory_to_programs_table.php
```

- **R10.2**: Métodos up() y down() siempre implementados
- **R10.3**: Foreign keys con constraints:
```php
$table->foreignId('currency_id')->constrained()->onDelete('cascade');
```

### 10.2 Seeders

**REGLAS:**
- **R10.4**: Seeders para datos esenciales:
  - AdminUserSeeder (usuario admin por defecto)
  - CurrencySeeder (monedas del sistema)
  - Datos de prueba separados

### 10.3 Índices y Performance

**REGLAS:**
- **R10.5**: Índices en campos de búsqueda frecuente:
```php
$table->index(['status', 'created_at']);
```

- **R10.6**: Usar Eloquent relationships para evitar N+1 queries:
```php
$applications = Application::with(['user', 'program'])->get();
```

---

## 📊 Checklist de Cumplimiento

### Al crear nueva funcionalidad:
- [ ] ¿Sigue las convenciones de nomenclatura?
- [ ] ¿Tiene tests apropiados?
- [ ] ¿Está documentado correctamente?
- [ ] ¿Implementa seguridad adecuada?
- [ ] ¿Maneja errores correctamente?
- [ ] ¿Sigue la estructura de carpetas establecida?

### Al hacer PR:
- [ ] ¿Tests pasan?
- [ ] ¿Documentación actualizada?
- [ ] ¿Commit messages siguen formato?
- [ ] ¿No hay secrets hardcodeados?
- [ ] ¿Performance es adecuada?

### Antes de despliegue:
- [ ] ¿Migraciones probadas?
- [ ] ¿Variables de entorno configuradas?
- [ ] ¿Backup realizado?
- [ ] ¿Plan de rollback definido?

---

**Última actualización**: 2025-01-15
**Versión**: 1.0
**Contacto**: Crear issue en GitHub para consultas o sugerencias
