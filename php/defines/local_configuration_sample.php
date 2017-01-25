<?php
namespace Attack;

// db vars
define('DB_HOST', 'localhost');
define('DB_USER', 'username');
define('DB_PASSWORD', 'password');
define('DB_NAME', 'database');

// server vars
define('DOMAIN_ORIGIN', 'http://localhost');
define('ABS_REF_PREFIX', '/');

// misc
define('ADMIN_MAIL', '');

/** @var array $log4php_config used in index.php */
global $log4php_config;
$log4php_config = array();
