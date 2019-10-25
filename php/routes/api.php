<?php

namespace Attack;

use Attack\Model\User\ModelUser;
use Attack\Tools\AuthMiddleware;

global $app;

$app->add(new AuthMiddleware());
$app->group('/api', function () use ($app) {
    $app->get('/me', function () use ($app) {
        $app->response->setBody(json_encode([
            'user' => ModelUser::getCurrentUser()->getLogin(),
            'id' => ModelUser::getCurrentUser()->getId(),
        ]));
    });

    $app->options('/:path', function () use ($app) {
        $res = $app->response;
        $res->headers->set('Content-Type', 'application/json');
        $res->headers->set('Access-Control-Allow-Origin', '*');
        $res->headers->set('Access-Control-Allow-Credentials', 'true');
        $res->headers->set('Access-Control-Max-Age', '60');
        $res->headers->set('Access-Control-Allow-Headers', 'AccountKey, x-requested-with, Content-Type, origin, authorization, accept, client-security-token, host, date, cookie, cookie2');
        $res->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    });
});
