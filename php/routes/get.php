<?php
namespace AttOn;

$app->get('/', function() use ($app, $debug) {
    $data = array();
    $data['user'] = Model\User\ModelUser::getCurrentUser()->getViewData();
    $app->render('main.twig', $data);
});

$app->get('/logout/', function() use ($app, $debug) {
    Controller\User\UserActions::logout();

    $data = array();
    $data['user'] = Model\User\ModelUser::getCurrentUser()->getViewData();
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
