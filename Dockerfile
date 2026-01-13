FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    sqlite3 \
    libsqlite3-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install \
        zip \
        pdo \
        pdo_sqlite \
        mbstring \
        xml \
        gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy project files
COPY . .

# Ensure SQLite DB directory exists
RUN mkdir -p database && touch database/database.sqlite

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Laravel permissions
RUN chmod -R 775 storage bootstrap/cache database

# Expose Render port
EXPOSE 10000

# Start Laravel
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=10000

