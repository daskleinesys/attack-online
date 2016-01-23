<?php
namespace AttOn;

$app->post('/login/', function() use ($app, $debug) {

    Controller\User\UserActions::login();

    // TODO : check if correct login data
    echo 'TODO : check if correct login data';

    // TODO : on error post error + main page
    echo 'TODO : on error post error + main page';

    // successfully logged in, redirect to main route
    $app->redirect(ABS_REF_PREFIX);
});
