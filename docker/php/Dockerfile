FROM php:fpm-alpine as production-stage

LABEL maintainer="liexa2024@gmail.com"

COPY /docker/php/php.ini /usr/local/etc/php/php.ini

RUN apk add --no-cache curl git build-base zlib-dev oniguruma-dev autoconf icu-dev bash

RUN apk add --no-cache \
        libjpeg-turbo-dev \
        libpng-dev \
        libwebp-dev \
        freetype-dev
RUN docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype
RUN docker-php-ext-install gd pdo pdo_mysql


COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Non root user
ARG PUID=1000
ARG PGID=1000
RUN apk --no-cache add shadow && \
    groupmod -o -g ${PGID} www-data && \
    usermod -o -u ${PUID} -g www-data www-data

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

# Git config
RUN git config --global user.email "liexa2024@gmail.com" \
    && git config --global user.name "PlexILIghT"

# Set user
USER www-data

# Files
COPY ./ /var/www
WORKDIR /var/www

# Run php-fpm
CMD php-fpm

# Expose port
EXPOSE 9000
