<?php
namespace AttOn;

$app->post('/login/', function() use ($app, $debug) {

    Controller\User\UserActions::login('username', 'password', false);

    // TODO : check if correct login data
    echo 'TODO : check if correct login data';

    // TODO : on error post error + main page
    echo 'TODO : on error post error + main page';

    // successfully logged in, redirect to main route
    $app->redirect(ABS_REF_PREFIX);
});

$app->post('/logout/', function() use ($app, $debug) {
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
