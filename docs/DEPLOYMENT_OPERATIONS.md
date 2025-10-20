# Guía de Deployment y Operaciones

## Tabla de Contenidos

1. [Configuración de Entornos](#configuración-de-entornos)
2. [Proceso de Deployment](#proceso-de-deployment)
3. [Scripts de Deployment](#scripts-de-deployment)
4. [Backup y Restauración](#backup-y-restauración)
5. [Monitoreo y Mantenimiento](#monitoreo-y-mantenimiento)
6. [Troubleshooting](#troubleshooting)
7. [Rollback Procedures](#rollback-procedures)

## Configuración de Entornos

### Requisitos del Sistema

#### Servidor de Producción
- **OS**: Ubuntu 20.04+ / CentOS 8+
- **PHP**: 8.2+ con extensiones: `php-mysql`, `php-redis`, `php-gd`, `php-zip`, `php-xml`
- **Web Server**: Nginx 1.18+ o Apache 2.4+
- **Database**: MySQL 8.0+ o PostgreSQL 14+
- **Cache**: Redis 6.0+
- **SSL**: Certificado válido (Let's Encrypt recomendado)

#### Configuración de Nginx

```nginx
server {
    listen 80;
    server_name ie.org.py www.ie.org.py;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name ie.org.py www.ie.org.py;
    
    ssl_certificate /etc/letsencrypt/live/ie.org.py/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/ie.org.py/privkey.pem;
    
    root /var/www/ie.org.py/public;
    index index.php index.html;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Strict-Transport-Security "max-age=63072000; includeSubDomains; preload" always;
    
    # API rate limiting
    location /api/ {
        limit_req zone=api burst=20 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP handling
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Static assets caching
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### Variables de Entorno

#### Producción (.env)
```bash
APP_NAME="IE Platform"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://ie.org.py

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=intercultural_experience_prod
DB_USERNAME=ie_user
DB_PASSWORD=secure_password_here

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=s3
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=noreply@ie.org.py
MAIL_PASSWORD=app_password_here
MAIL_ENCRYPTION=tls

AWS_ACCESS_KEY_ID=your_aws_key
AWS_SECRET_ACCESS_KEY=your_aws_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=ie-platform-files

SENTRY_LARAVEL_DSN=https://your-sentry-dsn@sentry.io
LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

SLACK_WEBHOOK_URL=https://hooks.slack.com/services/your/webhook/url
```

#### Staging (.env.staging)
```bash
APP_ENV=staging
APP_DEBUG=true
APP_URL=https://staging.ie.org.py
DB_DATABASE=intercultural_experience_staging
LOG_LEVEL=debug
```

## Proceso de Deployment

### GitHub Actions Workflow

El deployment se ejecuta automáticamente via GitHub Actions:

1. **Trigger**: Push a `main` (staging) o tag `v*` (production)
2. **Testing**: Ejecuta suite completa de tests
3. **Quality**: PHPStan, PHP-CS-Fixer, security scan
4. **Build**: Optimización de autoloader y cache
5. **Deploy**: Deployment automático con health checks

### Manual Deployment Steps

#### 1. Pre-deployment Checklist
```bash
# Verificar que todos los tests pasen
php artisan test

# Verificar migraciones pendientes
php artisan migrate:status

# Verificar configuración
php artisan config:check

# Backup de base de datos
./scripts/backup-database.sh
```

#### 2. Staging Deployment
```bash
# Ejecutar script de staging
./scripts/deploy-staging.sh

# Verificar deployment
curl -f https://staging.ie.org.py/api/health || echo "Health check failed"

# Ejecutar tests de integración
./scripts/run-integration-tests.sh staging
```

#### 3. Production Deployment
```bash
# Crear tag de release
git tag -a v1.0.1 -m "Release version 1.0.1"
git push origin v1.0.1

# O deployment manual
./scripts/deploy-production.sh

# Health check automático incluido en script
```

## Scripts de Deployment

### deploy-staging.sh

Deployment automático a servidor de staging:

```bash
#!/bin/bash
set -e

echo "🚀 Starting staging deployment..."

# Configuration
STAGING_SERVER="staging.ie.org.py"
STAGING_PATH="/var/www/staging.ie.org.py"
BACKUP_DIR="/backups/staging"

# Create backup
echo "📦 Creating database backup..."
mkdir -p $BACKUP_DIR
mysqldump -u staging_user -p staging_db > "$BACKUP_DIR/backup_$(date +%Y%m%d_%H%M%S).sql"

# Deploy code
echo "🔄 Syncing code to staging server..."
rsync -avz --delete \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='storage/logs' \
    ./ user@$STAGING_SERVER:$STAGING_PATH/

# Remote deployment commands
ssh user@$STAGING_SERVER << EOF
    cd $STAGING_PATH
    
    # Install/update dependencies
    composer install --no-dev --optimize-autoloader
    
    # Run migrations
    php artisan migrate --force
    
    # Clear and cache
    php artisan optimize:clear
    php artisan optimize
    
    # Restart services
    sudo systemctl reload php8.2-fpm
    sudo systemctl reload nginx
EOF

# Health check
echo "🏥 Running health check..."
sleep 5
if curl -f https://$STAGING_SERVER/api/health > /dev/null 2>&1; then
    echo "✅ Staging deployment successful!"
else
    echo "❌ Health check failed - rolling back..."
    # Add rollback logic here
    exit 1
fi
```

### deploy-production.sh

Deployment con zero-downtime y rollback automático:

```bash
#!/bin/bash
set -e

echo "🚀 Starting production deployment..."

# Configuration
PROD_SERVER="ie.org.py"
PROD_PATH="/var/www/ie.org.py"
BACKUP_DIR="/backups/production"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Pre-deployment backup
echo "📦 Creating full backup..."
./scripts/backup-database.sh production

# Create new release directory
RELEASE_DIR="$PROD_PATH/releases/$TIMESTAMP"
ssh user@$PROD_SERVER "mkdir -p $RELEASE_DIR"

# Deploy new code
echo "🔄 Deploying new release..."
rsync -avz --delete \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='storage' \
    ./ user@$PROD_SERVER:$RELEASE_DIR/

# Remote deployment
ssh user@$PROD_SERVER << EOF
    cd $RELEASE_DIR
    
    # Link shared directories
    ln -sf $PROD_PATH/shared/storage storage
    ln -sf $PROD_PATH/shared/.env .env
    
    # Install dependencies
    composer install --no-dev --optimize-autoloader
    
    # Run migrations (with backup point)
    php artisan migrate --force
    
    # Optimize for production
    php artisan optimize
    
    # Switch symlink atomically (zero-downtime)
    ln -sf $RELEASE_DIR $PROD_PATH/current_new
    mv $PROD_PATH/current_new $PROD_PATH/current
    
    # Restart services
    sudo systemctl reload php8.2-fpm nginx
EOF

# Health check and rollback if needed
echo "🏥 Running health checks..."
sleep 10

if ! curl -f https://$PROD_SERVER/api/health > /dev/null 2>&1; then
    echo "❌ Health check failed - initiating rollback..."
    ./scripts/rollback-production.sh
    exit 1
fi

echo "✅ Production deployment successful!"

# Cleanup old releases (keep last 5)
ssh user@$PROD_SERVER "ls -1dt $PROD_PATH/releases/* | tail -n +6 | xargs rm -rf"

# Send notification
curl -X POST $SLACK_WEBHOOK_URL -H 'Content-type: application/json' \
    --data "{\"text\":\"✅ Production deployment successful - Version $TIMESTAMP\"}"
```

## Backup y Restauración

### Backup Automático

Script ejecutado diariamente via cron:

```bash
# Crontab entry
0 2 * * * /var/www/ie.org.py/scripts/backup-database.sh production >> /var/log/backup.log 2>&1
```

### Backup Manual

```bash
# Backup completo (base de datos + archivos)
./scripts/backup-database.sh production

# Solo base de datos
mysqldump -u user -p intercultural_experience_prod > backup_$(date +%Y%m%d).sql

# Archivos de usuario
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/app/public/
```

### Restauración

#### Base de Datos
```bash
# Restaurar desde backup
mysql -u user -p intercultural_experience_prod < backup_20240101.sql

# Verificar restauración
php artisan migrate:status
```

#### Archivos
```bash
# Restaurar archivos
tar -xzf storage_backup_20240101.tar.gz -C /var/www/ie.org.py/

# Ajustar permisos
chown -R www-data:www-data storage/
chmod -R 755 storage/
```

## Monitoreo y Mantenimiento

### Health Checks

Endpoint de salud implementado en la API:

```php
// routes/api.php
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now(),
        'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
        'cache' => Cache::store()->put('health_check', 'ok', 60) ? 'working' : 'failed',
        'storage' => Storage::exists('health_check.txt') ? 'accessible' : 'failed'
    ]);
});
```

### Monitoring Commands

```bash
# Verificar servicios críticos
systemctl status nginx php8.2-fpm mysql redis

# Verificar logs de error
tail -f /var/log/nginx/error.log
tail -f storage/logs/laravel.log

# Verificar uso de recursos
htop
df -h
free -m

# Verificar conexiones de base de datos
mysql -e "SHOW PROCESSLIST;"

# Verificar queue workers
php artisan queue:monitor
```

### Automated Monitoring

#### Uptime Monitoring
```bash
# Script de monitoreo (ejecutar cada 5 min)
#!/bin/bash
URL="https://ie.org.py/api/health"
if ! curl -f $URL > /dev/null 2>&1; then
    # Enviar alerta
    curl -X POST $SLACK_WEBHOOK_URL -H 'Content-type: application/json' \
        --data "{\"text\":\"🚨 ALERT: IE Platform is DOWN! $URL\"}"
fi
```

#### Performance Monitoring
```bash
# Verificar tiempo de respuesta
curl -w "%{time_total}\n" -o /dev/null -s https://ie.org.py/api/programs

# Verificar base de datos
mysql -e "SHOW GLOBAL STATUS LIKE 'Slow_queries';"
```

## Troubleshooting

### Problemas Comunes

#### 1. Site Down / 500 Error
```bash
# Check logs
tail -50 storage/logs/laravel.log

# Check permissions
ls -la storage/
chown -R www-data:www-data storage/

# Clear cache
php artisan optimize:clear
```

#### 2. Database Connection Issues
```bash
# Test connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check MySQL status
systemctl status mysql
mysql -u root -p -e "SHOW PROCESSLIST;"
```

#### 3. High CPU/Memory Usage
```bash
# Check processes
top -c
ps aux | grep php

# Restart services
systemctl restart php8.2-fpm nginx

# Clear cache
redis-cli FLUSHALL
```

#### 4. SSL Certificate Issues
```bash
# Check certificate
openssl s_client -connect ie.org.py:443

# Renew Let's Encrypt
certbot renew --dry-run
certbot renew
```

### Performance Optimization

#### Database Optimization
```sql
-- Analyze slow queries
SHOW GLOBAL STATUS LIKE 'Slow_queries';

-- Check table sizes
SELECT table_name, ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.tables 
WHERE table_schema = 'intercultural_experience_prod'
ORDER BY (data_length + index_length) DESC;

-- Optimize tables
OPTIMIZE TABLE users, applications, programs;
```

#### Cache Optimization
```bash
# Warm up cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Check Redis memory
redis-cli INFO memory
```

## Rollback Procedures

### Automatic Rollback

Incluido en deployment script - si health check falla:

```bash
#!/bin/bash
# rollback-production.sh

echo "🔄 Starting rollback procedure..."

PROD_PATH="/var/www/ie.org.py"
BACKUP_DIR="/backups/production"

# Get previous release
PREVIOUS_RELEASE=$(ssh user@ie.org.py "ls -1t $PROD_PATH/releases/ | head -2 | tail -1")

if [ -z "$PREVIOUS_RELEASE" ]; then
    echo "❌ No previous release found!"
    exit 1
fi

echo "Rolling back to: $PREVIOUS_RELEASE"

ssh user@ie.org.py << EOF
    # Switch to previous release
    ln -sf $PROD_PATH/releases/$PREVIOUS_RELEASE $PROD_PATH/current_rollback
    mv $PROD_PATH/current_rollback $PROD_PATH/current
    
    # Restart services
    sudo systemctl reload php8.2-fpm nginx
EOF

# Health check
sleep 5
if curl -f https://ie.org.py/api/health > /dev/null 2>&1; then
    echo "✅ Rollback successful!"
else
    echo "❌ Rollback failed - manual intervention required!"
    exit 1
fi
```

### Manual Rollback

```bash
# List available releases
ssh user@ie.org.py "ls -la /var/www/ie.org.py/releases/"

# Rollback to specific release
ssh user@ie.org.py "ln -sf /var/www/ie.org.py/releases/20240101_120000 /var/www/ie.org.py/current"

# Restart services
ssh user@ie.org.py "sudo systemctl reload php8.2-fpm nginx"
```

### Database Rollback

```bash
# Restore from backup
mysql -u user -p intercultural_experience_prod < /backups/production/backup_20240101_120000.sql

# Run any necessary migrations
php artisan migrate:rollback --step=1
```

---

## Emergency Contacts

- **DevOps Lead**: +595 XXX XXXXXX
- **Database Admin**: +595 XXX XXXXXX  
- **Hosting Provider**: support@hosting.com
- **Slack Channel**: #ie-platform-ops

## Maintenance Schedule

- **Weekly**: Certificate renewal check
- **Monthly**: Security updates, dependency updates
- **Quarterly**: Performance review, capacity planning
- **Yearly**: Full security audit

---

*Última actualización: Enero 2024*
*Versión: 1.0.0*
