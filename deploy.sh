#!/bin/bash

echo "🚀 Deploying Intercultural Experience Platform..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    print_error "artisan file not found. Make sure you're in the Laravel project directory."
    exit 1
fi

print_status "Putting application into maintenance mode..."
php artisan down

print_status "Pulling latest changes from Git..."
git pull origin main

if [ $? -ne 0 ]; then
    print_error "Git pull failed!"
    php artisan up
    exit 1
fi

print_status "Installing/updating Composer dependencies..."
composer install --no-dev --optimize-autoloader

print_status "Running database migrations..."
php artisan migrate --force

print_status "Clearing application cache..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

print_status "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

print_status "Fixing file permissions..."
chmod -R 775 storage bootstrap/cache

print_status "Bringing application back online..."
php artisan up

print_status "✅ Deployment completed successfully!"
print_warning "Don't forget to test the application!"

echo ""
echo "📋 Post-deployment checklist:"
echo "  - Test main functionality"
echo "  - Check error logs"
echo "  - Verify database connectivity"
echo "  - Test mobile app connectivity"
echo "" 