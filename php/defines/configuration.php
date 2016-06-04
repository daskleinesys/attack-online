<?php

if (is_file(__DIR__ . DIRECTORY_SEPARATOR . 'local_configuration.php')) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'local_configuration.php';
}

if (!defined('DB_HOST')) {
    define('DB_HOST', $_ENV['RDS_HOSTNAME']);
}
if (!defined('DB_USER')) {
    define('DB_USER', $_ENV['RDS_USERNAME']);
}
if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', $_ENV['RDS_PASSWORD']);
}
if (!defined('DB_NAME')) {
    define('DB_NAME', $_ENV['RDS_DB_NAME']);
}

if (!defined('ABS_REF_PREFIX')) {
    define('ABS_REF_PREFIX', '/');
}

global $log4php_config;
if (!isset($log4php_config)) {
    $log4php_config = array();
}
