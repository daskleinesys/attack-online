<?php
namespace AttOn;

$app->post('/login/', function() use ($app, $debug) {
    // logout user if logged in
    Controller\User\UserActions::logout();

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
