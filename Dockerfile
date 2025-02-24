FROM php:8.2-cli-alpine

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

# Add application files
COPY src src
COPY composer.json composer.json

# We no longer need to do anything as root
USER www-data

RUN composer install --no-dev --no-scripts

# Use the default entrypoint for the base image
CMD ["docker-php-entrypoint"]
