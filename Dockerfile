FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libwebp-dev \
        libicu-dev \
        libonig-dev \
        libzip-dev \
        libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install \
        gd \
        intl \
        mbstring \
        mysqli \
        pdo \
        pdo_mysql \
        zip \
        dom \
        xml \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable mod_rewrite (for CI4 routing) and mod_headers (for proxy detection)
RUN a2enmod rewrite headers

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first for layer caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Copy application code
COPY . .

# Apache vhost: point DocumentRoot to public/, handle Traefik HTTPS forwarding
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Copy PHP production settings
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini

# Set writable directory permissions
RUN mkdir -p writable/cache writable/logs writable/session writable/uploads \
    && chmod -R 775 writable \
    && chown -R www-data:www-data writable public

# Persist sessions and export temp files across container restarts
VOLUME ["/var/www/html/writable/session", "/var/www/html/writable/logs", "/var/www/html/writable/uploads"]

EXPOSE 80
