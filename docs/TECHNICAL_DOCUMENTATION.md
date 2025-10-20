# Documentación Técnica - Intercultural Experience Platform

## Tabla de Contenidos

1. [Arquitectura del Sistema](#arquitectura-del-sistema)
2. [Stack Tecnológico](#stack-tecnológico)
3. [Estructura de Base de Datos](#estructura-de-base-de-datos)
4. [API REST](#api-rest)
5. [Autenticación y Autorización](#autenticación-y-autorización)
6. [Sistema de Archivos](#sistema-de-archivos)
7. [Testing y Quality Assurance](#testing-y-quality-assurance)
8. [CI/CD y Deployment](#cicd-y-deployment)
9. [Monitoreo y Logging](#monitoreo-y-logging)
10. [Seguridad](#seguridad)

## Arquitectura del Sistema

### Descripción General

La plataforma Intercultural Experience es un sistema dividido en tres componentes principales:

1. **Backend Laravel**: API REST + Panel administrativo web
2. **Aplicación Móvil**: React Native con Expo
3. **Base de Datos**: MySQL/PostgreSQL con migraciones automáticas

### Diagrama de Arquitectura

```
┌─────────────────────┐    ┌─────────────────────┐
│   React Native      │    │   Panel Admin       │
│   Mobile App        │    │   (Laravel Web)     │
│                     │    │                     │
└─────────┬───────────┘    └─────────┬───────────┘
          │                          │
          │ API REST                 │ Web Routes
          │                          │
          └─────────┬──────────────────┘
                    │
          ┌─────────▼───────────┐
          │   Laravel Backend   │
          │                     │
          │  - Controllers      │
          │  - Models           │
          │  - Middleware       │
          │  - Services         │
          │                     │
          └─────────┬───────────┘
                    │
          ┌─────────▼───────────┐
          │   MySQL Database    │
          │                     │
          │  - 27 Tablas        │
          │  - Relaciones       │
          │  - Índices          │
          │                     │
          └─────────────────────┘
```

## Stack Tecnológico

### Backend
- **Framework**: Laravel 12.0
- **PHP**: 8.2+
- **Database**: MySQL 8.0 / PostgreSQL 14+
- **Authentication**: Laravel Sanctum
- **File Storage**: Laravel Storage (local/S3)
- **Queue System**: Redis/Database
- **Cache**: Redis/Memcached

### Frontend Móvil
- **Framework**: React Native 0.76.9
- **Platform**: Expo
- **Language**: TypeScript
- **State Management**: Context API / Redux Toolkit
- **Navigation**: React Navigation v6
- **HTTP Client**: Axios

### DevOps & Tools
- **CI/CD**: GitHub Actions
- **Code Quality**: PHPStan, PHP-CS-Fixer
- **Testing**: PHPUnit, Pest
- **Documentation**: Swagger/OpenAPI
- **Monitoring**: Laravel Telescope, Sentry
- **Deployment**: SSH, Docker (opcional)

## Estructura de Base de Datos

### Tablas Principales

#### Usuarios y Autenticación
- `users` - Información básica de usuarios
- `personal_access_tokens` - Tokens de Laravel Sanctum
- `password_reset_tokens` - Tokens para reset de contraseña
- `sessions` - Sesiones de usuario

#### Programas y Aplicaciones
- `programs` - Programas de intercambio (IE/YFU)
- `applications` - Aplicaciones de usuarios a programas
- `application_documents` - Documentos subidos por usuarios
- `application_requisites` - Requisitos dinámicos por programa

#### Sistema de Formularios
- `program_forms` - Definición de formularios dinámicos
- `form_submissions` - Respuestas de usuarios a formularios
- `form_fields` - Campos de formularios (JSON schema)

#### Sistema de Puntos y Recompensas
- `points` - Puntos ganados por usuarios
- `rewards` - Catálogo de recompensas
- `redemptions` - Canjes de puntos por recompensas

#### Sistema Financiero
- `currencies` - Monedas soportadas
- `financial_transactions` - Transacciones financieras
- `exchange_rates` - Tasas de cambio

#### Soporte y Comunicación
- `support_tickets` - Sistema de tickets de soporte
- `notifications` - Notificaciones push/email
- `activity_logs` - Log de actividades del sistema

### Relaciones Clave

```sql
-- Usuario tiene muchas aplicaciones
users (1) -> (N) applications

-- Aplicación pertenece a un programa
applications (N) -> (1) programs

-- Aplicación tiene muchos documentos
applications (1) -> (N) application_documents

-- Usuario puede tener puntos y redenciones
users (1) -> (N) points
users (1) -> (N) redemptions

-- Formularios dinámicos por programa
programs (1) -> (N) program_forms
program_forms (1) -> (N) form_submissions
```

## API REST

### Estructura de Rutas

#### Autenticación
```
POST /api/register       - Registro de usuario
POST /api/login          - Inicio de sesión
POST /api/logout         - Cerrar sesión
GET  /api/profile        - Perfil de usuario
PUT  /api/profile        - Actualizar perfil
```

#### Programas
```
GET  /api/programs       - Listar programas
GET  /api/programs/{id}  - Detalle de programa
```

#### Aplicaciones
```
GET    /api/applications           - Mis aplicaciones
POST   /api/applications           - Crear aplicación
GET    /api/applications/{id}      - Detalle aplicación
PUT    /api/applications/{id}      - Actualizar aplicación
DELETE /api/applications/{id}      - Eliminar aplicación
```

#### Documentos
```
GET    /api/applications/{id}/documents     - Documentos de aplicación
POST   /api/applications/{id}/documents     - Subir documento
DELETE /api/documents/{id}                  - Eliminar documento
```

### Códigos de Respuesta Estándar

- `200` - OK - Operación exitosa
- `201` - Created - Recurso creado
- `400` - Bad Request - Error en datos de entrada
- `401` - Unauthorized - No autenticado
- `403` - Forbidden - Sin permisos
- `404` - Not Found - Recurso no encontrado
- `422` - Unprocessable Entity - Errores de validación
- `429` - Too Many Requests - Rate limiting
- `500` - Internal Server Error - Error del servidor

### Formato de Respuesta Estándar

```json
{
    "status": "success|error",
    "message": "Mensaje descriptivo",
    "data": { ... },
    "errors": { ... }
}
```

## Autenticación y Autorización

### Laravel Sanctum

El sistema utiliza Laravel Sanctum para autenticación API:

1. **Registro**: Usuario se registra, obtiene token
2. **Login**: Validación de credenciales, token renovado
3. **API Calls**: Token en header `Authorization: Bearer {token}`
4. **Logout**: Revocación de token actual

### Roles y Permisos

- **Admin**: Acceso completo al panel administrativo
- **User**: Solo acceso a API móvil, sin panel admin

### Middleware de Seguridad

1. `auth:sanctum` - Validación de token
2. `AdminMiddleware` - Solo administradores
3. `ValidateJsonInput` - Sanitización de entrada
4. `ThrottleRequests` - Rate limiting
5. `ActivityLogger` - Log de actividades

## Sistema de Archivos

### Configuración de Storage

```php
// config/filesystems.php
'disks' => [
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
    ],
    's3' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
    ],
]
```

### Manejo de Documentos

1. **Upload**: Validación de tipo, tamaño, virus scan
2. **Storage**: Organización por usuario/aplicación
3. **Access**: URLs firmadas para seguridad
4. **Backup**: Sincronización con S3

## Testing y Quality Assurance

### Test Suites

1. **Unit Tests** (`tests/Unit/`)
   - Validation Rules
   - Model Methods
   - Helper Functions

2. **Feature Tests** (`tests/Feature/`)
   - Authentication Flow
   - API Endpoints
   - Authorization Rules
   - Input Validation
   - Rate Limiting
   - Middleware Behavior

3. **Integration Tests**
   - End-to-end User Flows
   - Database Transactions
   - File Upload/Download
   - Email/Notifications

### Code Quality Tools

```bash
# PHPStan - Static Analysis
vendor/bin/phpstan analyse

# PHP-CS-Fixer - Code Style
vendor/bin/php-cs-fixer fix

# PHPUnit - Testing
php artisan test

# Coverage Report
php artisan test --coverage
```

### Continuous Integration

GitHub Actions ejecuta automáticamente:
- Tests unitarios y de integración
- Análisis de código con PHPStan
- Verificación de estilo con PHP-CS-Fixer
- Security scanning con Enlightn
- Coverage reports

## CI/CD y Deployment

### Pipeline de GitHub Actions

1. **Testing Stage**
   - Setup PHP 8.2, Node.js
   - Install dependencies
   - Run PHPUnit tests
   - Generate coverage

2. **Quality Stage**
   - PHPStan analysis
   - PHP-CS-Fixer check
   - Security scanning

3. **Build Stage**
   - Optimize autoloader
   - Cache configuration
   - Asset compilation

4. **Deploy Stage**
   - Deploy to staging (auto)
   - Deploy to production (manual)
   - Database migrations
   - Health checks

### Deployment Scripts

```bash
# Staging Deployment
./scripts/deploy-staging.sh

# Production Deployment
./scripts/deploy-production.sh

# Database Backup
./scripts/backup-database.sh
```

### Environment Management

- **Development**: Local con SQLite
- **Testing**: In-memory SQLite
- **Staging**: MySQL con datos de prueba
- **Production**: MySQL con SSL, backups automáticos

## Monitoreo y Logging

### Laravel Telescope

Monitoreo en tiempo real de:
- Requests HTTP
- Database queries
- Queue jobs
- Cache operations
- Exceptions

### Activity Logging

Registro automático de:
- Login/logout de usuarios
- Creación/modificación de aplicaciones
- Subida/eliminación de documentos
- Cambios administrativos

### Error Tracking

- **Laravel Log**: `storage/logs/laravel.log`
- **Sentry**: Error tracking en producción
- **Slack**: Notificaciones de errores críticos

## Seguridad

### Medidas Implementadas

1. **Input Validation**
   - Form Requests con validación estricta
   - Sanitización de entrada JSON
   - XSS/SQL Injection prevention

2. **Authentication Security**
   - Contraseñas fuertes obligatorias
   - Rate limiting en login
   - Token expiration (30 días)

3. **Authorization**
   - Role-based access control
   - Middleware de autorización
   - Separación admin/user estricta

4. **Data Protection**
   - Cifrado de datos bancarios
   - HTTPS obligatorio en producción
   - Secure headers (HSTS, CSP, etc.)

5. **File Security**
   - Validación estricta de uploads
   - Virus scanning
   - URLs firmadas para acceso

### Security Headers

```php
// Middleware SecurityHeaders
'X-Frame-Options' => 'DENY',
'X-Content-Type-Options' => 'nosniff',
'X-XSS-Protection' => '1; mode=block',
'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
'Content-Security-Policy' => "default-src 'self'",
```

### Rate Limiting

```php
// Rate limits por endpoint
Route::middleware(['throttle:auth'])->group(function () {
    Route::post('/login')->name('login');     // 5 attempts/minute
    Route::post('/register')->name('register'); // 3 attempts/minute
});

Route::middleware(['throttle:api'])->group(function () {
    // API endpoints: 60 requests/minute
});
```

---

## Comandos Útiles

### Desarrollo
```bash
# Iniciar servidor de desarrollo
php artisan serve

# Ejecutar migraciones
php artisan migrate

# Seed de base de datos
php artisan db:seed

# Generar documentación API
php artisan l5-swagger:generate

# Limpiar cache
php artisan optimize:clear
```

### Testing
```bash
# Ejecutar todos los tests
php artisan test

# Tests específicos
php artisan test --filter AuthTest

# Tests con coverage
php artisan test --coverage-html coverage
```

### Deployment
```bash
# Optimizar para producción
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Backup de base de datos
php artisan backup:run

# Queue worker
php artisan queue:work
```

---

*Documentación actualizada: Enero 2024*
*Versión: 1.0.0*
