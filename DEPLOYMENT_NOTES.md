# 📝 Notas de Deployment - Intercultural Experience Platform

**Fecha:** 12 de Octubre, 2025  
**Versión:** 1.0  
**Última Actualización:** Hoy

---

## 🏗️ Arquitectura de Ambientes

### Ambiente de Desarrollo

**Ubicación:** Local (macOS)  
**Servidor:** Homebrew  
**Ruta:** `/opt/homebrew/var/www/intercultural-experience`  
**Base de Datos:** MySQL local  
**URL:** `http://localhost/intercultural-experience/public`

**Propósito:**
- Desarrollo de nuevas funcionalidades
- Testing local
- Debugging
- Preparación de cambios para producción

---

### Ambiente de Producción

**Estado:** ✅ Activo  
**Deployment:** Manual (subida de archivos)  
**Sincronización:** Manual desde desarrollo

**Propósito:**
- Aplicación en vivo para usuarios finales
- Datos reales
- Operación 24/7

---

## ⚠️ Problemas Actuales del Proceso de Deployment

### 🔴 Riesgos Críticos

1. **Deployment Manual**
   - Archivos se suben manualmente
   - Alto riesgo de error humano
   - Sin validación automática
   - Posible inconsistencia entre archivos

2. **Sin Ambiente de Staging**
   - No hay ambiente intermedio para testing
   - Cambios van directo de desarrollo a producción
   - Sin validación en ambiente similar a producción

3. **Sin Rollback Automático**
   - Si algo falla, rollback manual
   - Posible downtime prolongado
   - Sin versiones guardadas automáticamente

4. **Sin Sincronización de Base de Datos**
   - Migraciones deben ejecutarse manualmente en producción
   - Riesgo de inconsistencia de esquema
   - Sin rollback de migraciones

5. **Sin Tests Pre-Deployment**
   - No se ejecutan tests antes de subir
   - Bugs pueden llegar a producción
   - Sin validación de código

---

## 📋 Proceso Actual de Deployment (Manual)

### Checklist Pre-Deployment

**Antes de subir a producción:**

- [ ] **Código testeado localmente**
  - [ ] Funcionalidad probada en desarrollo
  - [ ] Sin errores en logs
  - [ ] Tests unitarios pasando (si existen)

- [ ] **Base de Datos**
  - [ ] Migraciones probadas localmente
  - [ ] Seeders actualizados (si aplica)
  - [ ] Backup de BD de producción creado

- [ ] **Archivos**
  - [ ] Código commiteado en Git
  - [ ] .env de producción actualizado (si cambió)
  - [ ] Dependencias actualizadas (composer.lock, package-lock.json)

- [ ] **Configuración**
  - [ ] APP_DEBUG=false en producción
  - [ ] APP_ENV=production
  - [ ] Cache limpiado localmente

---

### Pasos de Deployment Manual

**1. Preparación Local**
```bash
# En desarrollo
cd /opt/homebrew/var/www/intercultural-experience

# Asegurar que todo está commiteado
git status
git add .
git commit -m "Descripción de cambios"

# Ejecutar tests (si existen)
php artisan test

# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimizar para producción
composer install --optimize-autoloader --no-dev
npm run build
```

**2. Backup de Producción**
```bash
# CRÍTICO: Siempre hacer backup antes de deployment
# Backup de base de datos
# Backup de archivos (especialmente storage/)
```

**3. Subida de Archivos**
```bash
# Método actual: Manual (FTP, SFTP, SCP, etc.)
# Archivos a subir:
# - app/
# - config/
# - database/migrations/
# - public/ (excepto storage)
# - resources/
# - routes/
# - composer.json
# - package.json
# - .env (actualizar, no sobrescribir)
```

**4. Comandos en Producción**
```bash
# SSH a servidor de producción
ssh user@production-server

# Navegar al directorio
cd /path/to/production

# Instalar dependencias
composer install --optimize-autoloader --no-dev
npm install --production

# Ejecutar migraciones
php artisan migrate --force

# Limpiar y optimizar cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Reiniciar queue workers (si aplica)
php artisan queue:restart
```

**5. Verificación Post-Deployment**
- [ ] Sitio carga correctamente
- [ ] Login funciona
- [ ] Funcionalidades críticas funcionan
- [ ] No hay errores en logs
- [ ] Base de datos actualizada correctamente

---

## 🚨 Rollback Manual (En Caso de Error)

### Pasos de Rollback

**1. Identificar el Problema**
```bash
# Revisar logs
tail -f storage/logs/laravel.log

# Revisar errores de servidor
tail -f /var/log/apache2/error.log
```

**2. Restaurar Archivos**
```bash
# Restaurar desde backup
# O revertir commit en Git y re-deployar
git revert HEAD
# Subir archivos anteriores
```

**3. Restaurar Base de Datos**
```bash
# Restaurar desde backup
mysql -u user -p database_name < backup.sql

# O revertir migraciones
php artisan migrate:rollback --step=1
```

**4. Verificar**
- [ ] Sitio funcional
- [ ] Sin errores
- [ ] Notificar al equipo

---

## 💡 Recomendaciones para Mejorar el Proceso

### Prioridad CRÍTICA (Implementar YA)

**1. Crear Ambiente de Staging**
```
Desarrollo → Staging → Producción
```

**Beneficios:**
- Testing en ambiente similar a producción
- Validación antes de deployment
- Reduce riesgo de errores

**Setup Recomendado:**
- Subdominio: `staging.tudominio.com`
- Base de datos separada
- Configuración idéntica a producción

---

**2. Implementar Git Deployment**

```bash
# En servidor de producción
cd /var/www/production
git init
git remote add origin https://github.com/user/repo.git

# Deployment con Git
git pull origin main
composer install --no-dev
php artisan migrate --force
php artisan optimize
```

**Beneficios:**
- Versionamiento automático
- Rollback fácil (git revert)
- Historial de cambios
- Menos errores que FTP manual

---

**3. Script de Deployment Automatizado**

```bash
#!/bin/bash
# deploy.sh

echo "🚀 Starting deployment..."

# Backup
echo "📦 Creating backup..."
php artisan backup:run

# Pull latest code
echo "📥 Pulling latest code..."
git pull origin main

# Install dependencies
echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader
npm install --production
npm run build

# Run migrations
echo "🗄️ Running migrations..."
php artisan migrate --force

# Clear and cache
echo "🧹 Clearing cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
echo "🔄 Restarting services..."
php artisan queue:restart

echo "✅ Deployment completed!"
```

**Uso:**
```bash
chmod +x deploy.sh
./deploy.sh
```

---

**4. Implementar CI/CD (Ideal)**

**GitHub Actions Example:**
```yaml
name: Deploy to Production

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Run tests
      run: php artisan test
    
    - name: Deploy to production
      if: success()
      run: |
        ssh user@server 'cd /var/www && ./deploy.sh'
```

**Beneficios:**
- Deployment automático al hacer push
- Tests ejecutados automáticamente
- Rollback automático si tests fallan
- Notificaciones de deployment

---

## 📊 Comparación de Métodos

| Aspecto | Manual (Actual) | Git Deploy | CI/CD (Ideal) |
|---------|----------------|------------|---------------|
| **Tiempo** | 30-60 min | 10-15 min | 5 min |
| **Riesgo de Error** | Alto | Medio | Bajo |
| **Rollback** | Difícil | Fácil | Automático |
| **Testing** | Manual | Manual | Automático |
| **Downtime** | 5-10 min | 2-5 min | < 1 min |
| **Costo Setup** | $0 | $0 | $0-50/mes |
| **Esfuerzo Setup** | 0h | 4h | 40h |

---

## 🎯 Plan de Mejora Recomendado

### Fase 1: Inmediato (Esta Semana)

**1. Crear Script de Deployment** (4 horas)
- Automatizar pasos manuales
- Reducir errores
- Documentar proceso

**2. Documentar Proceso Actual** (2 horas)
- Checklist detallado
- Procedimiento de rollback
- Contactos de emergencia

**3. Configurar Backups Automáticos** (4 horas)
- Backup diario de BD
- Backup de archivos críticos
- Testing de restore

---

### Fase 2: Corto Plazo (2 Semanas)

**4. Implementar Git Deployment** (8 horas)
- Configurar Git en producción
- Migrar a deployment con Git
- Documentar nuevo proceso

**5. Crear Ambiente de Staging** (16 horas)
- Configurar servidor/subdominio
- Clonar configuración de producción
- Proceso de testing en staging

---

### Fase 3: Mediano Plazo (1-2 Meses)

**6. Implementar CI/CD** (40 horas)
- GitHub Actions o GitLab CI
- Tests automatizados
- Deployment automático
- Notificaciones

---

## 📝 Notas Importantes

### Archivos que NO se deben subir

```
# .gitignore debe incluir:
.env
.env.backup
.phpunit.result.cache
node_modules/
vendor/
storage/*.key
.DS_Store
```

### Archivos Críticos a Respaldar

```
- .env (configuración de producción)
- storage/app/ (archivos subidos)
- storage/logs/ (logs históricos)
- Base de datos completa
```

### Comandos Útiles

```bash
# Ver último deployment (Git)
git log -1

# Ver diferencias con producción
git diff production/main

# Crear tag de versión
git tag -a v1.0.0 -m "Release 1.0.0"
git push origin v1.0.0

# Backup rápido de BD
php artisan backup:run --only-db

# Ver estado de migraciones
php artisan migrate:status

# Rollback última migración
php artisan migrate:rollback --step=1
```

---

## 🆘 Contactos de Emergencia

**En caso de problemas en producción:**

1. **Desarrollador Principal:** [Nombre] - [Email/Teléfono]
2. **DevOps/Sysadmin:** [Nombre] - [Email/Teléfono]
3. **Hosting Provider:** [Soporte] - [Email/Teléfono]

**Procedimiento de Emergencia:**
1. Evaluar severidad del problema
2. Si es crítico: Rollback inmediato
3. Notificar al equipo
4. Investigar causa raíz
5. Documentar incidente
6. Implementar prevención

---

## 📚 Referencias

- [Laravel Deployment Documentation](https://laravel.com/docs/deployment)
- [Git Deployment Guide](https://git-scm.com/book/en/v2/Git-on-the-Server-Getting-Git-on-a-Server)
- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Deployer PHP](https://deployer.org/) - Herramienta de deployment

---

**Última Actualización:** 12 de Octubre, 2025  
**Próxima Revisión:** Después de implementar mejoras

