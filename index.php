<?php
namespace AttOn;

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
require_once __DIR__ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Tools' . DIRECTORY_SEPARATOR . 'AttonAutoloader.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'defines' . DIRECTORY_SEPARATOR . 'defines.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'defines' . DIRECTORY_SEPARATOR . 'gamevars.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'defines' . DIRECTORY_SEPARATOR . 'local_configuration.php';

// INIT ERROR-HANDLING && LOGGING
\Logger::configure($log4php_config);
$logger = \Logger::getLogger('index.php');

// INIT SLIM APP
$app = new \Slim\Slim( array('debug' => $debug, 'view' => new \Slim\Views\Twig()));
$view = $app->view();
$view->parserExtensions = array(new \Twig_Extension_Debug());
$view->parserOptions = array('debug' => $debug);

// DEFINE SLIM-ERROR HANDLING
$app->error(function(\Exception $e) use ($app, $logger) {
    $logger->error($e->getMessage());
    $app->render('error.twig');
});
$app->notFound(function() use ($app) {
    $app->render('404.twig');
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

// SET GLOBAL VARS
$app->view->setData(array('debug' => $debug, 'nomerge' => $nomerge, 'absRefPrefix' => ABS_REF_PREFIX));
$app->run();
