<?php
namespace AttOn;
use AttOn\Controller\User\UserActions;
use AttOn\Tools\HeaderViewHelper;

$app->get('/', function() use ($app, $debug) {
    $data = array();
    HeaderViewHelper::parseCurrentUser($data);
    $data['template'] = 'home';
    $app->render('main.twig', $data);
});

$app->get('/login/', function() use ($app, $debug) {
    UserActions::logout();
    $data['template'] = 'login';
    $app->render('main.twig', $data);
});

$app->get('/logout/', function() use ($app, $debug) {
    UserActions::logout();
    $app->redirect(ABS_REF_PREFIX);
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
