#!/bin/sh

# Run Laravel migrations automatically
# The --force flag is required for production
echo "Running migrations..."
php artisan migrate --force

# Seed the database if needed (Optional: uncomment if you have seeders)
# php artisan db:seed --force

# Start FrankenPHP and serve the application
echo "Starting FrankenPHP..."
exec frankenphp php-server -l :80
