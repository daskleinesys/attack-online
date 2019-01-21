<?php

if (is_file(__DIR__ . DIRECTORY_SEPARATOR . 'local_configuration.php')) {
    include_once __DIR__ . DIRECTORY_SEPARATOR . 'local_configuration.php';
}

// db vars
if (!defined('DB_HOST')) {
    if (isset($_ENV['DB_HOST'])) {
        define('DB_HOST', $_ENV['DB_HOST']);
    } else if (isset($_ENV['RDS_HOSTNAME'])) {
        define('DB_HOST', $_ENV['RDS_HOSTNAME']);
    }
}
if (!defined('DB_USER')) {
    if (isset($_ENV['DB_USER'])) {
        define('DB_USER', $_ENV['DB_USER']);
    } else if (isset($_ENV['RDS_USERNAME'])) {
        define('DB_USER', $_ENV['RDS_USERNAME']);
    }
}
if (!defined('DB_PASSWORD')) {
    if (isset($_ENV['DB_PASSWORD'])) {
        define('DB_PASSWORD', $_ENV['DB_PASSWORD']);
    } else if (isset($_ENV['RDS_PASSWORD'])) {
        define('DB_PASSWORD', $_ENV['RDS_PASSWORD']);
    }
}
if (!defined('DB_NAME')) {
    if (isset($_ENV['DB_NAME'])) {
        define('DB_NAME', $_ENV['DB_NAME']);
    } else if (isset($_ENV['RDS_DB_NAME'])) {
        define('DB_NAME', $_ENV['RDS_DB_NAME']);
    }
}

// server vars
if (!defined('DOMAIN_ORIGIN')) {
    define('DOMAIN_ORIGIN', 'http://attack.thespielplatz.com');
}
if (!defined('ABS_REF_PREFIX')) {
    define('ABS_REF_PREFIX', '/');
}

// misc
if (!defined('ADMIN_MAIL')) {
    define('ADMIN_MAIL', '');
}

global $log4php_config;
if (!isset($log4php_config)) {
    $log4php_config = array();
}
