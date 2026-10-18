FROM dunglas/frankenphp:builder-php8.3.21

# Set Caddy server name to "http://" to serve on 80 and not 443
# Read more: https://frankenphp.dev/docs/config/#environment-variables
ENV SERVER_NAME="http://"

RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    git \
    unzip \
    librabbitmq-dev \
    libpq-dev \
    nano

RUN install-php-extensions \
    gd \
    pcntl \
    opcache \
    pdo \
    pdo_mysql \
    zip \
    redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=oven/bun:latest /usr/local/bin/bun /usr/local/bin/bun

WORKDIR /var/www/html

# Copy the Laravel application files into the container.
COPY . .

# Start with base PHP config, then add extensions.
COPY ./deploy/php.ini /usr/local/etc/php/
# COPY ./.docker/etc/supervisor.d/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Install PHP extensions
# RUN pecl install xdebug

# Install Laravel dependencies using Composer.
RUN composer install --optimize-autoloader --no-dev

COPY package.json ./
COPY bun.lock ./

# Build frontend assets
RUN bun install
RUN bun run build

# Laravel artisan commands
RUN php artisan storage:link && php artisan optimize

# Enable PHP extensions
# RUN docker-php-ext-enable xdebug

# Set permissions for Laravel.
RUN chown -R www-data:www-data storage bootstrap/cache

ENV FRANKENPHP_THREADS=8
ENV OP_CACHE_ENABLE=1

EXPOSE 7011

# Start Supervisor.
# CMD ["/usr/bin/supervisord", "-n", "-c",  "/etc/supervisor/conf.d/supervisord.conf"]