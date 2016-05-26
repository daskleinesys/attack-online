#!/usr/bin/env bash

# start new docker-container with mysql database
docker run --name attack-db -d -p 3306:3306 \
    -v $(pwd)/mysqldumps:/docker-entrypoint-initdb.d \
    -e MYSQL_ROOT_PASSWORD=attack \
    -e MYSQL_USER=attack \
    -e MYSQL_PASSWORD=attack \
    -e MYSQL_DATABASE=attack \
    mariadb:latest --character-set-server=utf8 --collation-server=utf8_unicode_ci

# create new docker-image for php-server
docker build -t attack .

# start new docker-container using the php-server docker-image just created
docker run --name attack --link attack-db:mysql -d -p 80:80 \
    -e XDEBUG_CONFIG="remote_host=192.168.99.1" \
    -v $(pwd)/dist:/var/www/html/dist \
    -v $(pwd)/php:/var/www/html/php \
    -v $(pwd)/templates:/var/www/html/templates \
    -v $(pwd)/vendor:/var/www/html/vendor \
    -v $(pwd)/.htaccess:/var/www/html/.htaccess \
    -v $(pwd)/favicon.ico:/var/www/html/favicon.ico \
    -v $(pwd)/index.php:/var/www/html/index.php \
    attack

# optionally install linked php-my-admin which can be reached at port 8080
docker run --name attack-pma --link attack-db:mysql -d -p 8080:80 \
    -e PMA_HOST=attack-db \
    phpmyadmin/phpmyadmin
