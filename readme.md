# Attack Online

> Boardgame Attack played online.


## Setup

1. get sources from github (https://github.com/daskleinesys/atton)
2. make composer update -> `php composer.phar update`
3. build dist assets -> `npm install && grunt`
4. if necessary : create **php/defines/local_configuration.php** from **php/defines/local_configuration_sample.php**
5. setup webserver and database from **mysqldumps/attack.sql** OR use docker for local setup, therefore take a look at `docker-setup.sh`
6. have fun :-)
