<?php
namespace AttOn;
use AttOn\Model\User\ModelUser;
use AttOn\Tools\Autoloader;
use AttOn\View\Content\Factories\GamesFactory;
use AttOn\View\Content\Factories\GameInfoFactory;
use AttOn\View\Content\Factories\NewGameFactory;
use AttOn\Exceptions\SessionException;

$app->map('/games(/:type)(/)', function($type = null) use ($app, $debug, $logger) {
    if ($type === null || empty($type)) {
        $type = 'new';
    }

    $data = array(
        'type' => $type
    );
    $factory = new GamesFactory();
    $view = $factory->getOperation();
    $view->run($data);
    $data['user'] = ModelUser::getCurrentUser()->getViewData();
    $app->render('main.twig', $data);
})->via('GET', 'POST')->name('games');

$app->map('/gameinfo(/:id_game)(/)', function($id_game = null) use ($app, $debug, $logger) {
    if ($id_game === null || empty($id_game)) {
        if (isset($_POST['id_game'])) {
            $app->redirect(ABS_REF_PREFIX . 'gameinfo/' . $_POST['id_game'] . '/', 200);
        } else {
            $app->redirect(ABS_REF_PREFIX . 'games/', 200);
        }
    }
    $id_game = (int) $id_game;

    $data = array(
        'id_game' => $id_game
    );
    $factory = new GameInfoFactory();
    $view = $factory->getOperation();
    $view->run($data);
    $data['user'] = ModelUser::getCurrentUser()->getViewData();
    $app->render('main.twig', $data);
})->via('GET', 'POST')->name('gameinfo');

$app->get('/newgame(/)', function() use ($app, $debug, $logger) {
    $data = array();
    $factory = new NewGameFactory();
    $view = $factory->getOperation();
    $view->run($data);
    $data['user'] = ModelUser::getCurrentUser()->getViewData();
    $app->render('main.twig', $data);
});

$app->map('/:content/', function($content) use ($app, $debug, $logger) {
    $data = array();

    // factory pattern
    $env = $app->environment();
    $factories = Autoloader::loadFactories($env['basepath'] . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . 'Content' . DIRECTORY_SEPARATOR . 'Factories' . DIRECTORY_SEPARATOR, '\\AttOn\\View\\Content\\Factories\\');

    // get operation
    foreach ($factories as $factory) {
        if ($factory->getName() === $content) {
            try {
                $content_object = $factory->getOperation();
            } catch (SessionException $ex) {
                $logger->error($ex);
                $data['user'] = ModelUser::getCurrentUser()->getViewData();
                $data['errors'] = array(
                    'message' => $ex->getMessage()
                );
                $app->render('error.twig', $data);
                return;
            }
        }
    }

    // render 404
    if (!isset($content_object)) {
        $app->notFound();
    }

    // run operation
    $content_object->run($data);
    $data['user'] = ModelUser::getCurrentUser()->getViewData();
    $app->render('main.twig', $data);
})->via('GET', 'POST')->name('content');
