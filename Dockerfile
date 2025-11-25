# Use PHP 8.1 with Apache
FROM php:8.1-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    default-mysql-client \
    zip \
    unzip \
    nodejs \
    npm \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable Apache mod_rewrite and mod_remoteip for reverse proxy support
RUN a2enmod rewrite remoteip

# Increase Apache request line limit to handle long URLs (prevent 414 errors)
RUN echo "LimitRequestLine 16384" >> /etc/apache2/apache2.conf

# Configure PHP error handling
# Enable error display for debugging (can be disabled in production via environment variable)
# Errors are always logged regardless of display setting
RUN echo "log_errors = On" >> /usr/local/etc/php/conf.d/owa.ini && \
    echo "error_log = /var/log/apache2/php_errors.log" >> /usr/local/etc/php/conf.d/owa.ini && \
    echo "display_errors = On" >> /usr/local/etc/php/conf.d/owa.ini && \
    echo "display_startup_errors = On" >> /usr/local/etc/php/conf.d/owa.ini && \
    echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/owa.ini

# Copy Apache configuration
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Copy application files
COPY . .

# Create config file from template if it doesn't exist (during build)
# This ensures the file exists even if the volume mount overwrites it
RUN if [ ! -f /var/www/html/owa-config.php ] && [ -f /var/www/html/owa-config-dist.php ]; then \
        cp /var/www/html/owa-config-dist.php /var/www/html/owa-config.php && \
        chmod 644 /var/www/html/owa-config.php; \
    fi

# Copy deployment initialization script
COPY deploy-init.sh /usr/local/bin/deploy-init.sh
RUN chmod +x /usr/local/bin/deploy-init.sh

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Install Node.js dependencies and build assets
RUN npm install && npm run build

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Create necessary directories with proper permissions
RUN mkdir -p /var/www/html/owa-data/caches \
    && mkdir -p /var/www/html/owa-data/logs \
    && mkdir -p /var/log/apache2 \
    && chown -R www-data:www-data /var/www/html/owa-data \
    && chmod -R 775 /var/www/html/owa-data \
    && chmod 777 /var/log/apache2

# Expose port 80
EXPOSE 80

# Start Apache with entrypoint
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

