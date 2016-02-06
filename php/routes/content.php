<?php
namespace AttOn;

$app->map('/:content/', function($content) use ($app, $debug, $logger) {
    $data = array();

    // factory pattern
    $env = $app->environment();
    $factories = Tools\Autoloader::loadFactories($env['basepath'] . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . 'Content' . DIRECTORY_SEPARATOR . 'Factories' . DIRECTORY_SEPARATOR, '\\AttOn\\View\\Content\\Factories\\');

    // get operation
    foreach ($factories as $factory) {
        if ($factory->getName() === $content) {
            try {
                $content_object = $factory->getOperation();
            } catch (Exceptions\SessionException $ex) {
                $logger->error($ex);
                $data['user'] = Model\User\ModelUser::getCurrentUser()->getViewData();
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
    $data['user'] = Model\User\ModelUser::getCurrentUser()->getViewData();
    $app->render('main.twig', $data);
})->via('GET', 'POST')->name('content');
