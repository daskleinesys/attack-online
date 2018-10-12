FROM php:7.1-apache
MAINTAINER Thomas Schagerl <thomas.schagerl@gmx.net>

# configure php
RUN apt-get update
RUN apt-get install -y vim
RUN apt-get install -y libxml2-dev
RUN apt-get install -y libfreetype6-dev
RUN apt-get install -y libjpeg62-turbo-dev
RUN apt-get install -y libmcrypt-dev
RUN apt-get install -y zlib1g-dev
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/
RUN docker-php-ext-install -j$(nproc) mysqli soap gd zip pdo pdo_mysql
RUN echo 'date.timezone = "Europe/Vienna"' > /usr/local/etc/php/php.ini

# setup xdebug
RUN yes | pecl install xdebug
RUN echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini
RUN echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini
RUN echo 'xdebug.remote_port="9000"' >> /usr/local/etc/php/conf.d/xdebug.ini
RUN echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini
RUN echo 'xdebug.idekey="PHPSTORM"' >> /usr/local/etc/php/conf.d/xdebug.ini

# configure apache as needed
RUN a2enmod rewrite
