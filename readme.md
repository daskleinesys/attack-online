# Attack Online

> Browsergame based on boardgame Attack. Not an exact clone.


## Setup

1. get sources from github (https://github.com/daskleinesys/attack-online)
2. switch into docker directory
    ```bash
    cd docker
    ```
3. start container
    ```bash
    docker-compose up -d
    ```
4. install php vendor files
    ```bash
    docker-compose exec web php composer.phar install
    ```
5. install js+css vendor files + build
    ```bash
    docker-compose exec web npm install
    docker-compose exec web npm run build
    ```
6. have fun :-)
