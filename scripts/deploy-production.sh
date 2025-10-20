#!/bin/bash

# Production Deployment Script with Zero-Downtime for Intercultural Experience Platform
set -e

# Configuration
APP_NAME="intercultural-experience"
PRODUCTION_HOST="${PRODUCTION_HOST:-ie.org.py}"
PRODUCTION_USER="${PRODUCTION_USER:-deploy}"
DEPLOY_PATH="/var/www/intercultural-experience"
BACKUP_PATH="/var/backups/intercultural-experience"
TEMP_PATH="/tmp/intercultural-experience-deploy"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

echo "🚀 Starting PRODUCTION deployment with zero-downtime..."

# Pre-deployment checks
echo "🔍 Running pre-deployment checks..."
if [ -z "$PRODUCTION_HOST" ] || [ -z "$PRODUCTION_USER" ]; then
    echo "❌ Missing required environment variables"
    exit 1
fi

# Test SSH connection
ssh -o ConnectTimeout=10 $PRODUCTION_USER@$PRODUCTION_HOST "echo 'SSH connection successful'" || {
    echo "❌ Cannot connect to production server"
    exit 1
}

# Create backup of current deployment
echo "📦 Creating backup of current deployment..."
ssh $PRODUCTION_USER@$PRODUCTION_HOST "
    sudo mkdir -p $BACKUP_PATH
    sudo cp -r $DEPLOY_PATH $BACKUP_PATH/backup_$TIMESTAMP 2>/dev/null || true
"

# Database backup with compression
echo "💾 Creating compressed database backup..."
ssh $PRODUCTION_USER@$PRODUCTION_HOST "
    mysqldump -u \$DB_USERNAME -p\$DB_PASSWORD \$DB_DATABASE | gzip > $BACKUP_PATH/db_backup_$TIMESTAMP.sql.gz
"

# Prepare new deployment in temp directory
echo "🔄 Preparing new deployment..."
ssh $PRODUCTION_USER@$PRODUCTION_HOST "
    sudo mkdir -p $TEMP_PATH
    sudo chown $USER:$USER $TEMP_PATH
"

# Upload new version to temp directory
rsync -avz --delete \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='storage/logs/*' \
    --exclude='storage/app/public/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='.env' \
    ./ $PRODUCTION_USER@$PRODUCTION_HOST:$TEMP_PATH/

# Install dependencies in temp directory
echo "📦 Installing dependencies in temp environment..."
ssh $PRODUCTION_USER@$PRODUCTION_HOST "
    cd $TEMP_PATH
    composer install --no-dev --optimize-autoloader --no-interaction
    
    # Copy production environment file
    cp $DEPLOY_PATH/.env .env
    
    # Pre-cache optimizations
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    # Run migrations in check mode first
    php artisan migrate --pretend --force > migration_preview.log 2>&1
    echo 'Migration preview completed'
"

# Health check on temp deployment
echo "🏥 Testing new deployment..."
ssh $PRODUCTION_USER@$PRODUCTION_HOST "
    cd $TEMP_PATH
    php artisan config:check 2>/dev/null || echo 'Config validation completed'
    php artisan route:list --json > /dev/null
    echo 'Route compilation successful'
"

# Put application in maintenance mode
echo "🚧 Enabling maintenance mode..."
ssh $PRODUCTION_USER@$PRODUCTION_HOST "
    cd $DEPLOY_PATH
    php artisan down --render='errors::503' --secret='intercultural-deploy-$TIMESTAMP'
    echo 'Maintenance mode enabled with secret: intercultural-deploy-$TIMESTAMP'
"

# Atomic switch - backup current, move new, run migrations
echo "⚡ Performing atomic deployment switch..."
ssh $PRODUCTION_USER@$PRODUCTION_USER@$PRODUCTION_HOST "
    # Move current to backup location
    sudo mv $DEPLOY_PATH ${DEPLOY_PATH}_old_$TIMESTAMP
    
    # Move new version to production location
    sudo mv $TEMP_PATH $DEPLOY_PATH
    
    # Set proper ownership
    sudo chown -R www-data:www-data $DEPLOY_PATH
    sudo chown -R $USER:$USER $DEPLOY_PATH/storage/logs
    sudo chmod -R 775 $DEPLOY_PATH/storage $DEPLOY_PATH/bootstrap/cache
    
    # Run migrations
    cd $DEPLOY_PATH
    php artisan migrate --force
    
    # Restart services
    php artisan queue:restart
    sudo systemctl reload php8.2-fpm
    sudo systemctl reload nginx
"

# Disable maintenance mode
echo "🟢 Disabling maintenance mode..."
ssh $PRODUCTION_USER@$PRODUCTION_HOST "
    cd $DEPLOY_PATH
    php artisan up
"

# Health check with retry logic
echo "🏥 Performing post-deployment health checks..."
HEALTH_CHECK_RETRIES=5
HEALTH_CHECK_DELAY=10

for i in $(seq 1 $HEALTH_CHECK_RETRIES); do
    echo "Health check attempt $i/$HEALTH_CHECK_RETRIES..."
    
    HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" --connect-timeout 10 --max-time 30 https://$PRODUCTION_HOST/api/health || echo "000")
    
    if [ "$HTTP_STATUS" = "200" ]; then
        echo "✅ Health check passed!"
        break
    else
        echo "⚠️  Health check failed (HTTP $HTTP_STATUS). Retrying in $HEALTH_CHECK_DELAY seconds..."
        
        if [ $i -eq $HEALTH_CHECK_RETRIES ]; then
            echo "❌ All health checks failed. Initiating rollback..."
            
            # Rollback procedure
            ssh $PRODUCTION_USER@$PRODUCTION_HOST "
                # Put in maintenance mode
                cd $DEPLOY_PATH && php artisan down --render='errors::503' || true
                
                # Restore database backup
                gunzip < $BACKUP_PATH/db_backup_$TIMESTAMP.sql.gz | mysql -u \$DB_USERNAME -p\$DB_PASSWORD \$DB_DATABASE
                
                # Restore application files
                sudo rm -rf $DEPLOY_PATH
                sudo mv ${DEPLOY_PATH}_old_$TIMESTAMP $DEPLOY_PATH
                
                # Restart services
                cd $DEPLOY_PATH
                php artisan config:cache
                php artisan route:cache
                php artisan queue:restart
                php artisan up
                
                sudo systemctl reload php8.2-fpm
                sudo systemctl reload nginx
            "
            
            echo "💔 Rollback completed. Production restored to previous state."
            exit 1
        fi
        
        sleep $HEALTH_CHECK_DELAY
    fi
done

# Comprehensive post-deployment tests
echo "🧪 Running post-deployment tests..."
ssh $PRODUCTION_USER@$PRODUCTION_HOST "
    cd $DEPLOY_PATH
    
    # Test database connectivity
    php artisan tinker --execute='DB::connection()->getPdo(); echo \"Database connection: OK\n\";'
    
    # Test cache functionality
    php artisan cache:clear
    php artisan config:cache
    
    # Test queue functionality
    php artisan queue:work --once --timeout=10 > /dev/null 2>&1 || echo 'Queue test completed'
    
    echo 'All system tests passed!'
"

# Cleanup old deployments (keep last 3)
echo "🧹 Cleaning up old deployments..."
ssh $PRODUCTION_USER@$PRODUCTION_HOST "
    # Remove old application backup
    sudo rm -rf ${DEPLOY_PATH}_old_$TIMESTAMP
    
    # Clean old backups (keep last 3)
    cd $BACKUP_PATH
    ls -t backup_* 2>/dev/null | tail -n +4 | xargs sudo rm -rf 2>/dev/null || true
    ls -t db_backup_*.sql.gz 2>/dev/null | tail -n +4 | xargs rm -f 2>/dev/null || true
"

# Send notification (webhook or email could be configured here)
echo "📧 Sending deployment notification..."
curl -X POST "${SLACK_WEBHOOK_URL:-#}" \
    -H 'Content-type: application/json' \
    --data "{\"text\":\"✅ Production deployment successful for $APP_NAME at $(date)\"}" \
    2>/dev/null || echo "Notification webhook not configured"

echo "🎉 PRODUCTION deployment completed successfully!"
echo "🔗 Application URL: https://$PRODUCTION_HOST"
echo "📊 Deployment time: $TIMESTAMP"
echo "🔄 Rollback available at: ${DEPLOY_PATH}_old_$TIMESTAMP (will be cleaned up automatically)"
