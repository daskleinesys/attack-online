<?php

namespace Attack;

use Attack\Model\Areas\ModelArea;
use Attack\Model\User\ModelUser;
use Attack\Tools\AuthMiddleware;

global $app;

function set_api_headers($app)
{
    $res = $app->response;
    $res->headers->set('Access-Control-Allow-Origin', '*');
    $res->headers->set('Content-Type', 'application/json');
}

$app->add(new AuthMiddleware());
$app->group('/api', function () use ($app) {
    $app->get('/me', function () use ($app) {
        set_api_headers($app);
        $app->response->setBody(json_encode([
            'user' => ModelUser::getCurrentUser()->getLogin(),
            'id' => ModelUser::getCurrentUser()->getId(),
        ]));
    });

    $app->get('/areas', function () use ($app) {
       set_api_headers($app);
       $iterator = ModelArea::iterator();
       $areas = [];
       while ($iterator->hasNext()) {
           $areas[] = $iterator->next();
       }
       $app->response->setBody(json_encode($areas));
    });

    $app->options('/:path', function () use ($app) {
        set_api_headers($app);
        $res = $app->response;
        $res->headers->set('Access-Control-Allow-Credentials', 'true');
        $res->headers->set('Access-Control-Max-Age', '60');
        $res->headers->set('Access-Control-Allow-Headers', 'AccountKey, x-requested-with, Content-Type, origin, authorization, accept, client-security-token, host, date, cookie, cookie2');
        $res->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    });
});
