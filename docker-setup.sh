#!/usr/bin/env bash

# setup docker network
docker network create --subnet 172.77.0.0/16 --driver bridge attack

# start new docker-container with mysql database
docker run -d \
    --name attack-db \
    --network=attack \
    -p 3306:3306 \
    -e MYSQL_ROOT_PASSWORD=attack \
    -e MYSQL_USER=attack \
    -e MYSQL_PASSWORD=attack \
    -e MYSQL_DATABASE=attack \
    -v $(pwd)/mysqldumps:/docker-entrypoint-initdb.d \
    mariadb:latest --character-set-server=utf8 --collation-server=utf8_unicode_ci

# create new docker-image for php-server
docker build -t attack .

# start new docker-container using the php-server docker-image just created
docker run -d \
    --name attack \
    --network=attack \
    -p 80:80 \
    -e XDEBUG_CONFIG="remote_host=172.77.0.1" \
    -v $(pwd)/dist:/var/www/html/dist \
    -v $(pwd)/php:/var/www/html/php \
    -v $(pwd)/templates:/var/www/html/templates \
    -v $(pwd)/vendor:/var/www/html/vendor \
    -v $(pwd)/.htaccess:/var/www/html/.htaccess \
    -v $(pwd)/favicon.ico:/var/www/html/favicon.ico \
    -v $(pwd)/index.php:/var/www/html/index.php \
    attack

# optionally install linked php-my-admin which can be reached at port 8080
docker run -d \
    --name attack-pma \
    --network=attack \
    -p 8080:80 \
    -e PMA_HOST=attack-db \
    phpmyadmin/phpmyadmin
