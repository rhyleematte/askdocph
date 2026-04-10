FROM dunglas/frankenphp:latest-php8.2

# Set the node version
ENV NODE_VERSION=18

# Install system dependencies and Node.js
RUN apt-get update && apt-get install -y \
    curl \
    unzip \
    git \
    && curl -sL https://deb.nodesource.com/setup_${NODE_VERSION}.x | bash - \
    && apt-get install -y nodejs

# Install PHP extensions (This is the most critical part)
# We use the built-in docker-php-ext-install or install-php-extensions
RUN install-php-extensions pdo_mysql bcmath gd intl zip opcache mbstring openssl

# Set working directory
WORKDIR /app

# Copy application files
COPY . .

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install PHP dependencies
RUN composer install --no-dev --ignore-platform-reqs

# Install NPM dependencies and compile assets
RUN npm install && npm run prod

# Set correct permissions for Laravel
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache
RUN chmod -R 775 /app/storage /app/bootstrap/cache

# Make startup script executable
RUN chmod +x /app/docker/startup.sh

# Expose the port Railway expects
EXPOSE 80

# Use the startup script to run migrations and start the server
ENTRYPOINT ["/app/docker/startup.sh"]
