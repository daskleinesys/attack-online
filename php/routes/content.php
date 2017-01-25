<?php
namespace Attack;

use Attack\Tools\Autoloader;
use Attack\Tools\HeaderViewHelper;
use Attack\View\Content\Factories\GamesFactory;
use Attack\View\Content\Factories\GameInfoFactory;
use Attack\View\Content\Factories\Interfaces\ContentFactory;
use Attack\View\Content\Factories\JoinGameFactory;
use Attack\View\Content\Factories\NewGameFactory;
use Attack\Exceptions\SessionException;
use Logger;
use Slim\Slim;

/* @var $app Slim */
/* @var $debug bool */
/* @var $logger Logger */
global $app, $debug, $logger;

$app->map('/games(/:type)(/)', function ($type = null) use ($app, $debug, $logger) {
    if ($type === null || empty($type)) {
        $type = 'new';
    }

    $data = array(
        'type' => $type
    );
    $factory = new GamesFactory();
    try {
        $view = $factory->getOperation();
        $view->run($data);
    } catch (SessionException $ex) {
        $data['errors'] = array(
            'message' => $ex->getMessage()
        );
        HeaderViewHelper::parseCurrentUser($data);
        $app->render('error.twig', $data);
        return;
    }
    HeaderViewHelper::parseCurrentUser($data);
    $app->render('main.twig', $data);
})->via('GET', 'POST')->name('games');

$app->map('/gameinfo(/:id_game)(/)', function ($id_game = null) use ($app, $debug, $logger) {
    if ($id_game === null || empty($id_game)) {
        if (isset($_POST['id_game'])) {
            $app->redirect(ABS_REF_PREFIX . 'gameinfo/' . $_POST['id_game'] . '/', 200);
        } else {
            $app->redirect(ABS_REF_PREFIX . 'games/', 200);
        }
    }
    $id_game = (int)$id_game;

    $data = array(
        'id_game' => $id_game
    );
    $factory = new GameInfoFactory();
    try {
        $view = $factory->getOperation();
        $view->run($data);
    } catch (SessionException $ex) {
        $data['errors'] = array(
            'message' => $ex->getMessage()
        );
        HeaderViewHelper::parseCurrentUser($data);
        $app->render('error.twig', $data);
        return;
    }
    HeaderViewHelper::parseCurrentUser($data);
    $app->render('main.twig', $data);
})->via('GET', 'POST')->name('gameinfo');

$app->map('/joingame(/:id_game)(/)', function ($id_game = null) use ($app, $debug, $logger) {
    if ($id_game === null || empty($id_game)) {
        if (isset($_POST['id_game'])) {
            $app->redirect(ABS_REF_PREFIX . 'joingame/' . $_POST['id_game'] . '/', 200);
        } else {
            $app->redirect(ABS_REF_PREFIX . 'games/', 200);
        }
    }
    $id_game = (int)$id_game;

    $data = array(
        'id_game' => $id_game
    );
    $factory = new JoinGameFactory();
    try {
        $view = $factory->getOperation();
        $view->run($data);
    } catch (SessionException $ex) {
        $data['errors'] = array(
            'message' => $ex->getMessage()
        );
        HeaderViewHelper::parseCurrentUser($data);
        $app->render('error.twig', $data);
        return;
    }
    HeaderViewHelper::parseCurrentUser($data);
    $app->render('main.twig', $data);
})->via('GET', 'POST')->name('joingame');

$app->get('/newgame(/)', function () use ($app, $debug, $logger) {
    $data = array();
    $factory = new NewGameFactory();
    try {
        $view = $factory->getOperation();
        $view->run($data);
    } catch (SessionException $ex) {
        $data['errors'] = array(
            'message' => $ex->getMessage()
        );
        HeaderViewHelper::parseCurrentUser($data);
        $app->render('error.twig', $data);
        return;
    }
    HeaderViewHelper::parseCurrentUser($data);
    $app->render('main.twig', $data);
});

$app->map('/:content/', function ($content) use ($app, $debug, $logger) {
    $data = array();

    // factory pattern
    $env = $app->environment();
    $factories = Autoloader::loadFactories($env['basepath'] . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . 'Content' . DIRECTORY_SEPARATOR . 'Factories' . DIRECTORY_SEPARATOR, '\\AttOn\\View\\Content\\Factories\\');

    // get operation
    foreach ($factories as $factory) {
        /* @var $factory ContentFactory */
        if ($factory->getName() === $content) {
            try {
                $view = $factory->getOperation();
                $view->run($data);
            } catch (SessionException $ex) {
                $logger->error($ex);
                $data['errors'] = array(
                    'message' => $ex->getMessage()
                );
                HeaderViewHelper::parseCurrentUser($data);
                $app->render('error.twig', $data);
                return;
            }
        }
    }
    HeaderViewHelper::parseCurrentUser($data);

    // render 404
    if (!isset($view)) {
        $app->notFound();
        $app->render('404.twig', $data);
        return;
    }
    $app->render('main.twig', $data);
})->via('GET', 'POST')->name('content');
