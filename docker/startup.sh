#!/bin/sh

# Ensure the storage and bootstrap/cache directories are writable at runtime
echo "Setting permissions..."
chmod -R 775 /app/storage /app/bootstrap/cache
chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Clear all caches
echo "Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Database Connectivity Test
echo "Testing Database Connection..."
php -r '
    $host = getenv("DB_HOST") ?: (getenv("MYSQLHOST") ?: (getenv("MYSQL_HOST") ?: "127.0.0.1"));
    $port = getenv("DB_PORT") ?: (getenv("MYSQLPORT") ?: (getenv("MYSQL_PORT") ?: "3306"));
    $db   = getenv("DB_DATABASE") ?: (getenv("MYSQLDATABASE") ?: (getenv("MYSQL_DATABASE") ?: "forge"));
    $user = getenv("DB_USERNAME") ?: (getenv("MYSQLUSER") ?: (getenv("MYSQL_USER") ?: "forge"));
    $pass = getenv("DB_PASSWORD") ?: (getenv("MYSQLPASSWORD") ?: (getenv("MYSQL_PASSWORD") ?: ""));

    echo "Attempting to connect to $host:$port...\n";

    try {
        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass, [PDO::ATTR_TIMEOUT => 5]);
        echo "✅ Connection Successful!\n";
    } catch (Exception $e) {
        echo "❌ Connection Failed: " . $e->getMessage() . "\n";
    }
'

# Run Laravel migrations automatically
echo "Running migrations..."
php artisan migrate --force

# Start FrankenPHP and serve the application from the public folder
echo "Starting FrankenPHP..."
exec frankenphp php-server -l :80 --root public
