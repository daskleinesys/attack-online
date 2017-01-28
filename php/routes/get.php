<?php
namespace Attack;

use Attack\GameLogic\CronMain;
use Attack\Controller\User\UserActions;
use Attack\Exceptions\MapException;
use Attack\Model\Game\ModelGame;
use Attack\Tools\HeaderViewHelper;
use Attack\View\Map;
use Logger;
use Slim\Slim;

/* @var $app Slim */
/* @var $debug bool */
/* @var $logger Logger */
global $app, $debug, $logger;

$app->get('/', function () use ($app, $debug) {
    $data = array();
    HeaderViewHelper::parseCurrentUser($data);
    $data['template'] = 'home';
    $app->render('main.twig', $data);
});

$app->get('/login/', function () use ($app, $debug) {
    UserActions::logout();
    $data['template'] = 'login';
    $app->render('main.twig', $data);
});

$app->get('/logout/', function () use ($app, $debug) {
    UserActions::logout();
    $app->redirect(ABS_REF_PREFIX);
});

$app->get('/map/', function () use ($app, $debug) {
    $data = array();
    HeaderViewHelper::parseCurrentUser($data);

    if (ModelGame::getCurrentGame() === null) {
        $data['errors'] = array(
            'message' => 'select a game first'
        );
        $app->render('error.twig', $data);
        return;
    }

    try {
        $map = new Map();
        $map->run($data);
    } catch (MapException $ex) {
        $data['errors'] = array(
            'message' => $ex->getMessage()
        );
        $app->render('error.twig', $data);
        return;
    }

    $app->render('map.twig', $data);
});

$app->get('/cron(/:id_game)(/)', function ($id_game = null) use ($app, $debug, $logger) {
    echo '<pre>';
    if (empty($id_game)) {
        $id_game = null;
        $logger->debug('running cron-calculation for all applicable games');
    } else if ($id_game !== null) {
        $id_game = (int)$id_game;
        $logger->debug('running cron-calculation for single game: ' . $id_game);
    }

    if ($debug && $id_game !== null) {
        $game = ModelGame::getGame($id_game);
        $game->setProcessing(false);
        $logger->debug('debug mode - forcing reset of processing status of single game: ' . $id_game);
    }

    $cron = new CronMain();
    $cron->execute($id_game);

    if ($cron->hasErrors()) {
        $msg = "cron failed for:\n    ";
        foreach ($cron->getErrors() as $key => $error) {
            $msg .= $key . ' - ' . $error . "\n    ";
        }
        $logger->error($msg);
    } else {
        $logger->debug('cron route successfully finished');
    }
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
