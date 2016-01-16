<?php

$app->get('/', function() use ($app, $debug) {
    $data = array();
    $app->render('main.twig', $data);
});
