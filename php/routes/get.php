<?php
namespace AttOn;

$app->get('/', function() use ($app, $debug) {
    $data = array();
    $data['user'] = Model\User\ModelUser::getCurrentUser()->getViewData();
    $data['template'] = 'home';
    $app->render('main.twig', $data);
});

$app->get('/login/', function() use ($app, $debug) {
    Controller\User\UserActions::logout();
    $data['template'] = 'login';
    $app->render('main.twig', $data);
});

$app->get('/logout/', function() use ($app, $debug) {
    Controller\User\UserActions::logout();
    $app->redirect(ABS_REF_PREFIX);
});

$app->get('/:content/', function($content) use ($app, $debug, $logger) {
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
});

/*
TODO Werner? concept + proposol for the following:
$app->get('/:content/')function($content) {
    new ContentFactory();
    $controller = ContentFactory->getController($content);

    if ($controller === null) {
        show404();
        return;
    }

    $view = ContentFactory->getView($content, get/ajax/rest);
    $output = $controller->run($data); // handle POST-data
    $view->run($output); // render page (inkl header/footer oder nur content oder rest-daten)

}
$app->get('/ajax/:content/');
*/
