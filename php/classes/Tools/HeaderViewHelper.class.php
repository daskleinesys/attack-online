<?php
namespace Attack\Tools;

use Attack\Model\Game\ModelGame;
use Attack\Model\User\ModelIsInGameInfo;
use Attack\Model\User\ModelUser;

class HeaderViewHelper {

    public static function parseCurrentUser(array &$data) {
        $user = ModelUser::getCurrentUser()->getViewData();

        $currGame = ModelGame::getCurrentGame();
        $user['games'] = array();
        $iter = ModelIsInGameInfo::iterator(ModelUser::getCurrentUser()->getId());
        while($iter->hasNext()) {
            $gameModel = ModelGame::getGame($iter->next()->getIdGame());
            if ($gameModel->getStatus() !== GAME_STATUS_STARTED && $gameModel->getStatus() !== GAME_STATUS_RUNNING) {
                continue;
            }
            $game = array(
                'id' => $gameModel->getId(),
                'name' => $gameModel->getName()
            );
            if ($currGame !== null && $currGame === $gameModel) {
                $game['selected'] = true;
            }
            $user['games'][] = $game;
        }
        if ($currGame === null) {
            $user['noGameSelected'] = true;
        } else {
            $user['currGame'] = $currGame->getViewData();
        }
        $data['user'] = $user;
    }

}
