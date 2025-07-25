FROM php:8.3-cli-alpine

# Install OS packages and PHP extensions
RUN apk add --no-cache git curl libzip-dev unzip sqlite-dev \
    && docker-php-ext-install pdo pdo_sqlite

# Copy Composer from the official image
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-ansi --no-interaction --no-progress --prefer-dist

# Expose the Laravel dev server port
EXPOSE 8000

# Start Laravel development server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]