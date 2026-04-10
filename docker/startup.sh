#!/bin/sh

# Ensure the storage and bootstrap/cache directories are writable at runtime
echo "Setting permissions..."
chmod -R 775 /app/storage /app/bootstrap/cache
chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Clear all caches for production performance
echo "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run Laravel migrations automatically
echo "Synchronizing database..."
php artisan migrate --force

# Start FrankenPHP and serve the application from the public folder
echo "Starting FrankenPHP Production Server..."
exec frankenphp php-server -l :80 --root public
