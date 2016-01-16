<?php
namespace AttOn;

// LOADING FRAMEWORKS
require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'defines' . DIRECTORY_SEPARATOR . 'defines.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'defines' . DIRECTORY_SEPARATOR . 'gamevars.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'defines' . DIRECTORY_SEPARATOR . 'local_configuration.php';

// SETUP TIMEZONE
date_default_timezone_set('Europe/Vienna');

// DEBUG
$debug = isset($_REQUEST['debug']) ? true : false;
if ($debug) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// NO-MERGE
$nomerge = isset($_REQUEST['no_merge']) || isset($_REQUEST['nomerge']) ? true : false;

// INIT SLIM APP
$app = new \Slim\Slim( array('debug' => $debug, 'view' => new \Slim\Views\Twig()));
$view = $app->view();
$view->parserExtensions = array(new \Twig_Extension_Debug());
$view->parserOptions = array('debug' => $debug);

// DEFINE ERROR HANDLING
$app->error(function(\Exception $e) use ($app) {
    $app->render('error.twig');
});
$app->notFound(function() use ($app) {
    $app->render('404.twig');
});

// DEFINE GET ROUTES
include_once __DIR__ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'get.php';

// DEFINE POST ROUTES
include_once __DIR__ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'post.php';

// SET GLOBAL VARS
$app->view->setData(array('debug' => $debug, 'nomerge' => $nomerge, 'absRefPrefix' => ABS_REF_PREFIX));
$app->run();
