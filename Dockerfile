FROM php:5.6-apache
MAINTAINER Thomas Schagerl <thomas.schagerl@gmx.net>

# Configure PHP
RUN apt-get update && \
    apt-get install -y \
        vim libxml2-dev libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng12-dev \
        zlib1g-dev
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ && \
    docker-php-ext-install -j$(nproc) mysqli soap gd zip pdo pdo_mysql
RUN echo 'date.timezone = "Europe/Vienna"' > /usr/local/etc/php/php.ini

# Configure Apache as needed
RUN a2enmod rewrite
