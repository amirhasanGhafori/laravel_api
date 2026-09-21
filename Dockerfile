FROM php:8.3-fpm as php

RUN apt-get update -y \
    && apt-get install -y \
        libzip-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        unzip \
        git \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install \
        gd \
        zip \
        pdo \
        pdo_mysql \
        bcmath \
    && pecl channel-update pecl.php.net \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

WORKDIR /var/www

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

COPY . .

ENV PORT=8000

ENTRYPOINT ["docker/entrypoint.sh"]