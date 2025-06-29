# 🌍 Intercultural Experience Platform

[![Laravel](https://img.shields.io/badge/Laravel-12.0-red.svg)](https://laravel.com)
[![React Native](https://img.shields.io/badge/React%20Native-0.76.9-blue.svg)](https://reactnative.dev)
[![PHP](https://img.shields.io/badge/PHP-8.2+-purple.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

Sistema integral para la gestión de programas de intercambio cultural con backend Laravel y aplicación móvil React Native.

## 📋 Descripción

La **Plataforma de Experiencias Interculturales** es un sistema completo que permite:

- 🎓 **Gestión de Programas** de intercambio internacional
- 📱 **Aplicación Móvil** para estudiantes y participantes
- 📊 **Panel Administrativo** para gestión y reportes
- 📝 **Constructor de Formularios** dinámico con drag & drop
- 💰 **Sistema de Puntos y Recompensas**
- 🎫 **Gestión de Aplicaciones** y documentación
- 💳 **Sistema Financiero** integrado

## 🏗️ Arquitectura

```
📦 intercultural-experience/
├── 🔧 Backend Laravel (API + Panel Admin)
│   ├── app/Http/Controllers/        # Controladores
│   ├── app/Models/                  # Modelos Eloquent
│   ├── database/migrations/         # Migraciones
│   ├── resources/views/             # Vistas Blade
│   └── routes/                      # Rutas API y Web
├── 📱 ieapp/ (React Native App)
│   ├── src/screens/                 # Pantallas
│   ├── src/components/              # Componentes reutilizables
│   ├── src/services/api/            # Servicios API
│   └── src/navigation/              # Navegación
└── 📄 IE-FORMS/                     # Formularios y documentos
```

## ✨ Características Principales

### 🖥️ Backend Laravel
- **API RESTful** para aplicación móvil
- **Panel Administrativo** completo
- **Constructor de Formularios** visual con drag & drop
- **Sistema de Autenticación** con Laravel Sanctum
- **Gestión de Archivos** y documentos
- **Reportes y Analytics**
- **Sistema de Notificaciones**

### 📱 Aplicación Móvil React Native
- **Autenticación** de usuarios
- **Exploración de Programas** disponibles
- **Aplicaciones** a programas
- **Sistema de Puntos** y recompensas
- **Perfil de Usuario** y configuraciones
- **Notificaciones Push**

### 🎨 Constructor de Formularios
- **Drag & Drop** intuitivo
- **Campos Dinámicos**: texto, email, fecha, archivos, etc.
- **Secciones Organizadas**
- **Vista Previa** en tiempo real
- **Validaciones** automáticas

## 🚀 Instalación

### Requisitos Previos

- **PHP** 8.2+
- **Composer**
- **Node.js** 18+
- **MySQL** o **PostgreSQL**
- **Expo CLI** (para la app móvil)

### 1. Clonar el Repositorio

```bash
git clone https://github.com/keuch2/intercultural-experience.git
cd intercultural-experience
```

### 2. Configurar Backend Laravel

```bash
# Instalar dependencias PHP
composer install

# Configurar variables de entorno
cp .env.example .env
# Editar .env con tu configuración de base de datos

# Generar clave de aplicación
php artisan key:generate

# Ejecutar migraciones
php artisan migrate

# Ejecutar seeders (datos de prueba)
php artisan db:seed

# Instalar dependencias Node.js
npm install

# Compilar assets
npm run build
```

### 3. Configurar Aplicación Móvil

```bash
cd ieapp

# Instalar dependencias
npm install

# Iniciar en modo desarrollo
npm start
```

## ⚙️ Configuración

### Variables de Entorno (.env)

```env
APP_NAME="Intercultural Experience"
APP_ENV=local
APP_KEY=base64:YOUR_KEY_HERE
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=intercultural_experience
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Configuración de archivos
FILESYSTEM_DISK=local

# Configuración de email
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
```

### Base de Datos

El sistema incluye migraciones completas y seeders para:
- Usuarios administradores
- Programas de ejemplo
- Monedas y configuraciones
- Datos de prueba

```bash
# Ejecutar migraciones con datos de prueba
php artisan migrate:fresh --seed
```

## 🎯 Uso

### Panel Administrativo

1. **Acceder**: `http://localhost/admin`
2. **Login**: Usar credenciales creadas en seeders
3. **Gestionar**:
   - Programas de intercambio
   - Aplicaciones de estudiantes
   - Formularios dinámicos
   - Sistema financiero
   - Reportes y analytics

### Aplicación Móvil

1. **Iniciar**: `npm start` en carpeta `ieapp/`
2. **Desarrollo**: Usar Expo Go en dispositivo físico
3. **Funciones**:
   - Registro y login de usuarios
   - Explorar programas disponibles
   - Aplicar a programas
   - Gestionar perfil y puntos

## 📊 Estructura de Base de Datos

### Principales Entidades

- **Users**: Usuarios del sistema
- **Programs**: Programas de intercambio
- **Applications**: Aplicaciones de usuarios
- **Points**: Sistema de puntuación
- **Rewards**: Recompensas disponibles
- **FormTemplates**: Plantillas de formularios
- **Currencies**: Monedas y costos

## 🛠️ Desarrollo

### Comandos Útiles

```bash
# Backend Laravel
php artisan serve              # Servidor de desarrollo
php artisan migrate:fresh      # Recrear base de datos
php artisan db:seed           # Ejecutar seeders
php artisan queue:work        # Procesar trabajos en cola

# Frontend (Aplicación móvil)
cd ieapp
npm start                     # Servidor Expo
npm run android              # Ejecutar en Android
npm run ios                  # Ejecutar en iOS
```

### Testing

```bash
# Tests PHP
php artisan test

# Tests JavaScript
cd ieapp
npm test
```

## 📱 Screenshots

| Panel Admin | App Móvil | Constructor Formularios |
|-------------|-----------|-------------------------|
| ![Admin](docs/admin-dashboard.png) | ![Mobile](docs/mobile-app.png) | ![Forms](docs/form-builder.png) |

## 🤝 Contribuir

1. Fork el proyecto
2. Crear rama feature (`git checkout -b feature/AmazingFeature`)
3. Commit cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abrir Pull Request

## 📝 Roadmap

- [ ] **Notificaciones Push** para la app móvil
- [ ] **Chat en tiempo real** entre usuarios
- [ ] **Integración con APIs** de universidades
- [ ] **Sistema de calificaciones** y reviews
- [ ] **Múltiples idiomas** (i18n)
- [ ] **Dashboard analytics** avanzado

## 🐛 Reportar Issues

Si encuentras algún bug o tienes sugerencias:
1. Revisa [issues existentes](https://github.com/keuch2/intercultural-experience/issues)
2. Crea un [nuevo issue](https://github.com/keuch2/intercultural-experience/issues/new)

## 📄 Licencia

Este proyecto está bajo la licencia MIT. Ver [LICENSE](LICENSE) para más detalles.

## 👥 Equipo

- **Desarrollo Backend**: Laravel + PHP
- **Desarrollo Frontend**: React Native + TypeScript
- **Base de Datos**: MySQL
- **Infraestructura**: XAMPP Local

---

⭐ **¿Te gusta el proyecto?** ¡Dale una estrella en GitHub!

📧 **Contacto**: [Crear issue](https://github.com/keuch2/intercultural-experience/issues/new) para consultas
