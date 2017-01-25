<?php
namespace AttOn;

use AttOn\Tools\HeaderViewHelper;
use Slim\Slim;
use Slim\Views\Twig;

// DEBUG && NO-MERGE
$debug = isset($_REQUEST['debug']) ? true : false;
if ($debug) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
$nomerge = isset($_REQUEST['no_merge']) || isset($_REQUEST['nomerge']) ? true : false;

// SETUP PHP
date_default_timezone_set('Europe/Vienna');
session_start();

// LOADING FRAMEWORKS
require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Tools' . DIRECTORY_SEPARATOR . 'Autoloader.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'defines' . DIRECTORY_SEPARATOR . 'defines.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'defines' . DIRECTORY_SEPARATOR . 'gamevars.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'defines' . DIRECTORY_SEPARATOR . 'configuration.php';

// INIT ERROR-HANDLING && LOGGING
global $log4php_config;
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
    Model\DataBase\SQLCommands::init();
} catch (Exceptions\DataSourceException $e) {
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
