#!/bin/bash

# Staging Deployment Script for Intercultural Experience Platform
set -e

# Configuration
APP_NAME="intercultural-experience"
STAGING_HOST="${STAGING_HOST:-staging.ie.org.py}"
STAGING_USER="${STAGING_USER:-deploy}"
DEPLOY_PATH="/var/www/intercultural-experience-staging"
BACKUP_PATH="/var/backups/intercultural-experience"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

echo "🚀 Starting deployment to staging environment..."

# Create backup of current deployment
echo "📦 Creating backup of current deployment..."
ssh $STAGING_USER@$STAGING_HOST "
    sudo mkdir -p $BACKUP_PATH
    sudo cp -r $DEPLOY_PATH $BACKUP_PATH/backup_$TIMESTAMP 2>/dev/null || true
"

# Database backup
echo "💾 Creating database backup..."
ssh $STAGING_USER@$STAGING_HOST "
    mysqldump -u \$DB_USERNAME -p\$DB_PASSWORD \$DB_DATABASE > $BACKUP_PATH/db_backup_$TIMESTAMP.sql
"

# Deploy new version
echo "🔄 Deploying new version..."
rsync -avz --delete \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='storage/logs/*' \
    --exclude='storage/app/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    ./ $STAGING_USER@$STAGING_HOST:$DEPLOY_PATH/

# Install dependencies and run deployment commands
echo "📦 Installing dependencies and updating application..."
ssh $STAGING_USER@$STAGING_HOST "
    cd $DEPLOY_PATH
    composer install --no-dev --optimize-autoloader --no-interaction
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan migrate --force
    php artisan queue:restart
    php artisan storage:link
    sudo chown -R www-data:www-data storage bootstrap/cache
    sudo chmod -R 775 storage bootstrap/cache
"

# Health check
echo "🏥 Performing health check..."
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" https://$STAGING_HOST/api/health || echo "000")

if [ "$HTTP_STATUS" = "200" ]; then
    echo "✅ Deployment successful! Staging environment is healthy."
    
    # Clean old backups (keep last 5)
    ssh $STAGING_USER@$STAGING_HOST "
        cd $BACKUP_PATH
        ls -t backup_* 2>/dev/null | tail -n +6 | xargs rm -rf 2>/dev/null || true
        ls -t db_backup_*.sql 2>/dev/null | tail -n +6 | xargs rm -f 2>/dev/null || true
    "
else
    echo "❌ Health check failed (HTTP $HTTP_STATUS). Rolling back..."
    
    # Rollback
    ssh $STAGING_USER@$STAGING_HOST "
        sudo rm -rf $DEPLOY_PATH
        sudo mv $BACKUP_PATH/backup_$TIMESTAMP $DEPLOY_PATH
        cd $DEPLOY_PATH
        php artisan config:cache
        php artisan route:cache
        php artisan queue:restart
    "
    
    exit 1
fi

echo "🎉 Staging deployment completed successfully!"
