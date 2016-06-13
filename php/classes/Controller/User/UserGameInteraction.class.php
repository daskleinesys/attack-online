<?php
namespace AttOn\Controller\User;

use AttOn\Controller\Interfaces\ConstrictedController;
use Logger;
use AttOn\Model\Game\ModelGame;
use AttOn\Exceptions\JoinUserException;
use AttOn\Exceptions\ControllerException;
use AttOn\Model\User\ModelIsInGameInfo;
use AttOn\Model\User\ModelUser;

class UserGameInteraction extends ConstrictedController {

    private $logger;

    public function __construct() {
        $this->logger = Logger::getLogger('UserGameInteraction');
    }

    /**
     * tries to join a game with the user
     *
     * @param int $id_color
     * @param int $id_game
     * @param string $password
     * @throws JoinUserException, NullPointerException
     * @return boolean
     */
    public function join($id_color = null, $id_game, $password = null) {
        $game = ModelGame::getGame($id_game);

        // check password
        if ($game->checkPasswordProtection() && !$game->checkPassword($password)) {
            throw new JoinUserException('Invalid password.');
        }
        if ($this->checkInGame($id_game)) {
            throw new JoinUserException('User already in this game!');
        }
        if ($game->getFreeSlots() <= 0) {
            throw new JoinUserException('Game is full.');
        }

        // join game
        ModelIsInGameInfo::joinGame(ModelUser::getCurrentUser()->getId(), $id_game, $id_color);
        return true;
    }

    /**
     * trys to leave the given game, returns true on success
     *
     * @param int $id_game
     * @throws ControllerException, NullPointerException
     * @return boolean
     */
    public function leaveGame($id_game) {
        $game = ModelGame::getGame($id_game);

        if ($game->getStatus() !== GAME_STATUS_NEW) {
            throw new ControllerException('Can\'t leave game. It has allready started.');
        }
        if ($game->checkProcessing()) {
            throw new ControllerException('Can\'t leave game. It has allready started.');
        }

        ModelIsInGameInfo::leaveGame(ModelUser::getCurrentUser()->getId(), $id_game);
        return true;
    }

    /**
     * selects a game and throws exception if game is not found
     *
     * @param int $id_game
     * @throws NullPointerException, ControllerException
     * @return boolean
     */
    public function selectGame($id_game) {
        if (!$this->checkInGame($id_game)) {
            throw new ControllerException('User is not in this game.');
        }
        $game = ModelGame::getGame($id_game);

        if ($game->getStatus() === GAME_STATUS_NEW) {
            throw new ControllerException('Unable to select game as it is not yet started.');
        }
        if ($game->getStatus() === GAME_STATUS_DONE) {
            throw new ControllerException('Unable to select game as it is already done.');
        }

        $_SESSION['id_game'] = $id_game;
        return true;
    }

}
