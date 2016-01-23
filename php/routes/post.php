<?php
namespace AttOn;

$app->post('/login/', function() use ($app, $debug) {

    try {
        $username = (isset($_POST['username'])) ? $_POST['username'] : '';
        $password = (isset($_POST['password'])) ? $_POST['password'] : '';
        $remember = (isset($_POST['remember'])) ? true : false;
        Controller\User\UserActions::login($username, $password, $remember);

        // successfully logged in, redirect to main route
        $app->redirect(ABS_REF_PREFIX);
    } catch (Exceptions\LoginException $ex) {
        $user = Model\User\ModelUser::getCurrentUser();

        $data = array();
        $data['user'] = $user->getViewData();
        $data['errors'] = array(
            'login' => $ex->getMessage()
        );

        if (isset($_POST['username']) && !empty($_POST['username'])) {
            $data['user']['username'] = $_POST['username'];
        }

        $app->render('main.twig', $data);
    }
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
