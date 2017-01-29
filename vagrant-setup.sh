#!/usr/bin/env bash

echo "-- install php7.1 --"
sudo apt-get update
sudo add-apt-repository ppa:ondrej/php
sudo apt-get install -y php7.1 php7.1-mysql libapache2-mod-php7.1 php-mbstring
sudo apt-get update
sudo a2dismod php5
sudo a2enmod php7.1

echo "-- setup vhost --"
sudo a2dissite 000-default
sudo a2dissite scotchbox.local
sudo cat <<EOT >> /etc/apache2/sites-available/attack.local.conf
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    ServerName attack.local
    DocumentRoot /var/www
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOT
sudo a2ensite attack.local

echo "-- restart apache --"
sudo apachectl restart

echo "-- init mysql db --"
mysql -uroot -proot -e "CREATE DATABASE attack;"
mysql -uroot -proot -e "CREATE USER 'attack'@'localhost' IDENTIFIED BY 'attack';"
mysql -uroot -proot -e "GRANT ALL PRIVILEGES ON attack.* TO 'attack'@'localhost';FLUSH PRIVILEGES;"
mysql -uattack -pattack attack < /var/www/mysqldumps/01_create.sql
mysql -uattack -pattack attack < /var/www/mysqldumps/02_insert.sql
mysql -uattack -pattack attack < /var/www/mysqldumps/local_user_games.sql
