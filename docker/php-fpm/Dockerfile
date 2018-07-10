FROM php:7.2-fpm

RUN apt-get update

RUN apt-get install -y zlib1g-dev libpq-dev git libicu-dev libxml2-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && docker-php-ext-install zip xml

RUN apt-get update -y \
    && apt-get install -y libgmp-dev re2c libmhash-dev libmcrypt-dev file \
    && ln -s /usr/include/x86_64-linux-gnu/gmp.h /usr/local/include/ \
    && docker-php-ext-configure gmp \
    && docker-php-ext-install gmp

RUN docker-php-ext-install -j$(nproc) bcmath

RUN docker-php-ext-install pcntl

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug;

COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
RUN echo "xdebug.remote_host=192.168.20.59" >> /usr/local/etc/php/conf.d/xdebug.ini

RUN curl --insecure https://getcomposer.org/composer.phar -o /usr/bin/composer && chmod +x /usr/bin/composer

# Set timezone
RUN rm /etc/localtime
RUN ln -s /usr/share/zoneinfo/Europe/Berlin /etc/localtime
RUN "date"

WORKDIR /opt/repux-plugins
