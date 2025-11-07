# --- Base PHP image (Debian-based)
FROM php:8.3-fpm-bullseye AS base

# Install system dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    unzip \
    zip \
    libssl-dev \
    libpq-dev \
    libsqlite3-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    curl \
    gcc g++ make autoconf \
 && docker-php-ext-install \
        pdo_mysql \
        pdo_pgsql \
        pdo_sqlite \
        zip \
        gd \
 && pecl install mongodb-1.15.1 \
 && docker-php-ext-enable mongodb \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

RUN echo "error_reporting = E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED" > /usr/local/etc/php/conf.d/99-hide-deprecated.ini \
 && echo "display_errors = Off" >> /usr/local/etc/php/conf.d/99-hide-deprecated.ini


# Configure PHP-FPM
RUN sed -i \
    -e "s|^;daemonize = yes|daemonize = no|" \
    -e "s|^;catch_workers_output = yes|catch_workers_output = yes|" \
    /usr/local/etc/php-fpm.conf

# Link nginx logs to stdout/stderr
RUN ln -sf /dev/stdout /var/log/nginx/access.log \
 && ln -sf /dev/stderr /var/log/nginx/error.log


# Remove the default site
RUN rm -f /etc/nginx/conf.d/default.conf

COPY nginx.conf /etc/nginx/nginx.conf

# Copy default nginx config (adjust path if your file differs)
COPY ./default.conf /etc/nginx/sites-available/default.conf


# Remove the default nginx site and enable ours
RUN rm -f /etc/nginx/sites-enabled/default \
    && ln -sf /etc/nginx/sites-available/default.conf /etc/nginx/sites-enabled/default

# --- Composer build stage ---
FROM base AS build

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/xhgui

COPY . /var/www/xhgui

# Ensure cache directory exists and is writable by www-data
RUN mkdir -p cache \
    && chown -R www-data:www-data cache \
    && chmod -R 775 cache

RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader


# --- Final runtime image ---
FROM base AS runtime

WORKDIR /var/www/xhgui

COPY --from=build /var/www/xhgui /var/www/xhgui

EXPOSE 80

# ✅ Start both nginx and php-fpm properly
CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]

