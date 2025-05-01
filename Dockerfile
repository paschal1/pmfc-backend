# Use official PHP 8.2 image with Apache
FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    curl

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Enable Apache rewrite module
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy entrypoint script
COPY entrypoint.sh /var/www/html/entrypoint.sh
RUN chmod +x /var/www/html/entrypoint.sh

# Copy project files
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose Apache Port
EXPOSE 80

# Set default entrypoint
ENTRYPOINT ["/var/www/html/entrypoint.sh"]

# Default command
CMD ["apache2-foreground"]
