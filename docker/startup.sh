#!/bin/sh

# Ensure the storage and bootstrap/cache directories are writable at runtime
# This is crucial in Docker environments where volumes might change ownership
echo "Setting permissions..."
chmod -R 775 /app/storage /app/bootstrap/cache
chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Clear all caches to prevent stale configurations from local dev
echo "Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Run Laravel migrations automatically
echo "Running migrations..."
php artisan migrate --force

# Start FrankenPHP and serve the application from the public folder
echo "Starting FrankenPHP..."
exec frankenphp php-server -l :80 --root public
