<?php
namespace AttOn\Controller\Interfaces;
use AttOn\Model\User\ModelUser;
use AttOn\Exceptions\GameAdministrationException;
use AttOn\Model\Game\ModelGame;
use AttOn\Model\User\ModelIsInGameInfo;

abstract class ConstrictedController {

    protected function checkAdmin() {
        $current_user = ModelUser::getCurrentUser();
        if ($current_user->getStatus() !== STATUS_USER_ADMIN) {
            throw new GameAdministrationException('non-admin.');
        }
        return true;
    }

    protected function checkMod() {
        $current_user = ModelUser::getCurrentUser();
        if ($current_user->getStatus() !== STATUS_USER_MODERATOR && $current_user->getStatus() !== STATUS_USER_ADMIN) {
            throw new GameAdministrationException('non-mod.');
        }
        return true;
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
