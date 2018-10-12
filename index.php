<?php

namespace Attack;

use Attack\Tools\HeaderViewHelper;
use Slim\Slim;
use Slim\Views\Twig;

global $debug, $nomerge, $log4php_config;

// DEBUG && NO-MERGE
$debug = false;
$nomerge = false;

// SETUP PHP
date_default_timezone_set('Europe/Vienna');
session_start();

// LOADING FRAMEWORKS
require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR .
    'Tools' . DIRECTORY_SEPARATOR . 'Autoloader.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'defines' . DIRECTORY_SEPARATOR .
    'defines.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'defines' . DIRECTORY_SEPARATOR .
    'gamevars.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'defines' . DIRECTORY_SEPARATOR .
    'configuration.php';

// DEBUG
if ($debug) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// INIT ERROR-HANDLING && LOGGING
\Logger::configure($log4php_config);
$logger = \Logger::getLogger('index.php');

// INIT SLIM APP
$app = new Slim(array('debug' => $debug, 'view' => new Twig()));
$view = $app->view();
$view->parserExtensions = array(new \Twig_Extension_Debug());
$view->parserOptions = array('debug' => $debug);
$env = $app->environment();
$env['basepath'] = __DIR__;

// DEFINE SLIM-ERROR HANDLING
$app->error(function (\Exception $e) use ($app, $logger) {
    $logger->error($e->getMessage());

    $data = array();
    HeaderViewHelper::parseCurrentUser($data);
    $app->render('error.twig', $data);
});
$app->notFound(function () use ($app) {
    $data = array();
    HeaderViewHelper::parseCurrentUser($data);
    $app->render('404.twig', $data);
});

// INIT DB-CONNECTION
try {
    Database\SQLCommands::init();
} catch (Exceptions\DatabaseException $e) {
    $logger->fatal($e->getMessage());
    $app->render('error.twig');
    die();
}

// DEFINE GET ROUTES
include_once __DIR__ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'get.php';

// DEFINE POST ROUTES
include_once __DIR__ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'post.php';

// DEFINE CONTENT ROUTES
include_once __DIR__ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'content.php';

// SET GLOBAL VARS
$app->view->setData(array('debug' => $debug, 'nomerge' => $nomerge, 'absRefPrefix' => ABS_REF_PREFIX));
Tools\CheckSessions::checkCookies();
Tools\CheckSessions::checkCurrentGame();

// RUN SLIM APP
try {
    $app->run();
} catch (\Exception $e) {
    $logger->fatal($e->getMessage());
    $app->render('error.twig');
}
