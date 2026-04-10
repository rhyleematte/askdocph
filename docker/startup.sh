#!/bin/sh

# Ensure the storage and bootstrap/cache directories are writable at runtime
# We use 777 here to ensure the web server (any user) has absolute access to these folders
echo "Applying aggressive permissions..."
chmod -R 777 /app/storage /app/bootstrap/cache
chown -R www-data:www-data /app/storage /app/bootstrap/cache

# CLEAR caches instead of caching (caching can cause 502/500 errors if env mismatch)
echo "Ensuring fresh environment..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Database Connectivity Test
echo "Testing Database Connection..."
php -r '
    $host = getenv("DB_HOST") ?: (getenv("MYSQLHOST") ?: (getenv("MYSQL_HOST") ?: "mysql.railway.internal"));
    $port = getenv("DB_PORT") ?: (getenv("MYSQLPORT") ?: (getenv("MYSQL_PORT") ?: "3306"));
    $db   = getenv("DB_DATABASE") ?: (getenv("MYSQLDATABASE") ?: (getenv("MYSQL_DATABASE") ?: "forge"));
    $user = getenv("DB_USERNAME") ?: (getenv("MYSQLUSER") ?: (getenv("MYSQL_USER") ?: "forge"));
    $pass = getenv("DB_PASSWORD") ?: (getenv("MYSQLPASSWORD") ?: (getenv("MYSQL_PASSWORD") ?: ""));

    echo "Attempting to connect to $host:$port as user \"$user\"...\n";

    try {
        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass, [PDO::ATTR_TIMEOUT => 5]);
        echo "✅ Connection Successful!\n";
    } catch (Exception $e) {
        echo "❌ Connection Failed: " . $e->getMessage() . "\n";
    }
'

# Run Laravel migrations automatically
echo "Synchronizing database..."
php artisan migrate --force

# HARD Reconstruct the storage shortcut
echo "Bridging image storage..."
rm -rf /app/public/storage
ln -s /app/storage/app/public /app/public/storage
echo "Bridge Status: "
ls -l /app/public/storage

# Start FrankenPHP and serve the application from the public folder
echo "Starting FrankenPHP Production Server..."
exec frankenphp php-server -l :80 --root public
