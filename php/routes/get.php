<?php
namespace AttOn;

$app->get('/', function() use ($app, $debug) {
    // TODO : instanciate controller that checks cookie/session and creates user model if applicable

    $user = Model\User\ModelUser::getCurrentUser();

    $data = array();
    $data['user'] = $user->getViewData();

    $app->render('main.twig', $data);
});

$app->get('/logout/', function() use ($app, $debug) {
	if (isset($_SESSION['user_id'])) {
		$_SESSION = array();
		if (isset($_COOKIE[session_name])) {
			setcookie(session_name(), '', time() - 3600);
		}
		session_destroy();
	}

	// If the user is logged in, delete the cookie to log them out
	if (isset($_COOKIE['user_id'])) {
		// Delete the user ID and username cookies by setting their expirations to an hour ago (3600)
		setcookie('user_id', '', time() - 3600);
		setcookie('user_name', '', time() - 3600);
		setcookie('user_status', '', time() - 3600);
	}

    $data = array();
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
