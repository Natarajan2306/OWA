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

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Increase Apache request line limit to handle long URLs (prevent 414 errors)
RUN echo "LimitRequestLine 16384" >> /etc/apache2/apache2.conf

# Copy Apache configuration
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Copy application files
COPY . .

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
    && chown -R www-data:www-data /var/www/html/owa-data \
    && chmod -R 775 /var/www/html/owa-data

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]

