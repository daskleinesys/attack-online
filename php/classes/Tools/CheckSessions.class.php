<?php
namespace AttOn\Tools;

use AttOn\Model\Game\ModelGame;
use AttOn\Model\User\ModelIsInGameInfo;
use AttOn\Model\User\ModelUser;
use AttOn\Exceptions\LoginException;
use AttOn\Exceptions\NullPointerException;

class CheckSessions {

    public static function checkCookies() {
        if (isset($_SESSION['id_user']) && (int)$_SESSION['id_user'] > 0) {
            ModelUser::setCurrentUser($_SESSION['id_user']);
            return true;
        }
        if (!isset($_COOKIE['user_token'])) {
            return false;
        }
        try {
            $user = ModelUser::loginWithToken($_COOKIE['user_token']);
            $_SESSION['id_user'] = $user->getId();
        } catch (LoginException $ex) {
            return false;
        }
        return true;
    }

    public static function checkCurrentGame() {
        // check if a user is logged in
        if (ModelUser::getCurrentUser()->getId() <= 0) {
            unset($_SESSION['id_game']);
            return;
        }

        // check if user selected another game
        if (isset($_POST['select_game'])) {
            $_SESSION['id_game'] =  (int) $_POST['select_game'];
        }

        if (!isset($_SESSION['id_game'])) {
            return;
        }

        // check if game exists and is running/started
        try {
            $game = ModelGame::getGame($_SESSION['id_game']);
        } catch(NullPointerException $ex) {
            unset($_SESSION['id_game']);
            return;
        }
        if ($game->getStatus() !== GAME_STATUS_STARTED && $game->getStatus() !== GAME_STATUS_RUNNING) {
            unset($_SESSION['id_game']);
            return;
        }

        // check if user is in this game
        try {
            ModelIsInGameInfo::getIsInGameInfo(ModelUser::getCurrentUser()->getId(), $_SESSION['id_game']);
        } catch(NullPointerException $ex) {
            unset($_SESSION['id_game']);
            return;
        }

        // set current game
        ModelGame::setCurrentGame($_SESSION['id_game']);
    }

}
