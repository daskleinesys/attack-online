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
    // factory pattern
    try {
        $env = $app->environment();
        $factories = Tools\Autoloader::loadFactories($env['basepath'] . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . 'Content' . DIRECTORY_SEPARATOR . 'Factories' . DIRECTORY_SEPARATOR, '\\AttOn\\View\\Content\\Factories\\');
    } catch (\Exception $ex) {
        if ($debug) {
            throw $ex;
        }
        $logger->error($ex);
        $data = array();
        $data['user'] = Model\User\ModelUser::getCurrentUser()->getViewData();
        $data['errors'] = array(
            'message' => 'Unable to load content. Please contact a site-admin.'
        );
        $app->render('error.twig', $data);
        return;
    }
    // TODO : make getoperation possible
    echo 'TODO : make getoperation possible';
    die('');

    foreach ($factories as $factory) {
        if ($factory->getName() == $content) {
            try {
                $content_object = $factory->getOperation($this->id_user,$this->id_game);
            } catch (SessionException $ex) {
                $logger->error($ex);
                $session_error = true;
                $content_object = new ContentHome($this->id_user,$this->id_game,'home', CHECK_SESSION_NONE);
                $error_msg[] = 'Sorry, you have no permission to view this site.';
            } catch (Exception $ex) {
                $logger->error($ex);
                $error_msg[] = 'Unable to load content. Please contact a site-admin.';
            }
        }
    }

    // TODO : rework render 404.twig
    if (!isset($content_object)) {
        $content_object = new ContentPageNotFound($this->id_user,$this->id_game,'pagenotfound',CHECK_SESSION_NONE);
    }

    // TODO : rework -> show error page
    // throw exception if no content is created
    if (!is_object($content_object)) {
        $logger->fatal('No Operation created!');
        throw new Exception('No Operation created!');
    }

    $data = array();
    $data['user'] = Model\User\ModelUser::getCurrentUser()->getViewData();
    $data['template'] = 'games';
    $app->render('main.twig', $data);
});

/*
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
