FROM php:8.1-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    libxml2-dev \
    git \
    unzip \
    bash

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        pdo_mysql \
        mysqli \
        zip \
        intl \
        mbstring \
        opcache \
        xml \
        bcmath

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# PHP configuration for production
RUN echo "memory_limit=256M" > /usr/local/etc/php/conf.d/memory.ini \
    && echo "upload_max_filesize=50M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size=50M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_execution_time=300" >> /usr/local/etc/php/conf.d/execution.ini \
    && echo "max_input_time=300" >> /usr/local/etc/php/conf.d/execution.ini

# OPcache configuration for production
RUN echo "opcache.enable=1" > /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=8" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.revalidate_freq=60" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=1" >> /usr/local/etc/php/conf.d/opcache.ini

# Set working directory
WORKDIR /var/www/html

# Create runtime directories with proper permissions
RUN mkdir -p /var/www/html/runtime /var/www/html/web/assets \
    && chown -R www-data:www-data /var/www/html

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
