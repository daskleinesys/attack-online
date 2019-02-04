<?php
namespace Attack\Controller\Interfaces;

use Attack\Model\User\ModelUser;
use Attack\Model\Game\ModelGame;
use Attack\Model\User\ModelIsInGameInfo;
use Attack\Exceptions\GameAdministrationException;

abstract class ConstrictedController {

    protected function checkAdmin() {
        $current_user = ModelUser::getCurrentUser();
        if ($current_user->getStatus() !== STATUS_USER_ADMIN) {
            throw new GameAdministrationException('non-admin.');
        }
        return true;
    }

    /**
     * @throws GameAdministrationException
     */
    protected function checkMod():void {
        $current_user = ModelUser::getCurrentUser();
        if ($current_user->getStatus() !== STATUS_USER_MODERATOR && $current_user->getStatus() !== STATUS_USER_ADMIN) {
            throw new GameAdministrationException('non-mod.');
        }
    }

    protected function checkCreator($id_game) {
        if (ModelGame::getGame($id_game)->getCreator() !== ModelUser::getCurrentUser()) {
            throw new GameAdministrationException('not-creator.');
        }
        return true;
    }

    protected function checkInGame($id_game) {
        return ModelIsInGameInfo::isUserInGame(ModelUser::getCurrentUser()->getId(), $id_game);
    }

}
