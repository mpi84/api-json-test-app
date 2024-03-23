FROM php:8.3-fpm-alpine

RUN apk add --no-cache make bash icu-dev postgresql-dev
RUN docker-php-ext-install intl pdo_mysql pdo_pgsql

RUN echo "short_open_tag=off" >> /usr/local/etc/php/conf.d/symfony.ini
RUN echo 'alias c="php bin/console"' >> ~/.bashrc

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=2.7.1
