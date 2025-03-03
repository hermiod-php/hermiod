# Define build stages for each PHP version
ARG PHP_VERSION=8.2
FROM php:${PHP_VERSION}-cli-alpine AS builder

# Linux configuration
RUN apk add --no-cache linux-headers ${PHPIZE_DEPS} \
    && pecl install xdebug \
    && docker-php-ext-install pcntl \
    && docker-php-ext-enable xdebug pcntl \
    && apk del linux-headers ${PHPIZE_DEPS} \
    && docker-php-source delete

# Copy composer and composer.json
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

# We no longer need to do anything as root
USER www-data

# Use the default entrypoint for the base image
CMD ["docker-php-entrypoint"]
