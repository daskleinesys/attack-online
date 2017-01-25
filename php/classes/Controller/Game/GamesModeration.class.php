<?php
namespace Attack\Controller\Game;

use Attack\Controller\Interfaces\ConstrictedController;
use Attack\Exceptions\GameAdministrationException;
use Attack\Exceptions\GameCreationException;
use Attack\Exceptions\JoinUserException;
use Attack\Model\Game\ModelGame;
use Attack\Model\User\ModelUser;
use Attack\Model\User\ModelIsInGameInfo;

class GamesModeration extends ConstrictedController {

    /**
     * try to create a new game, returns the game as model on success
     *
     * @param string $game_name
     * @param int $players
     * @param string $password1
     * @param string $password2
     * @param bool $creator_joins
     * @param int $id_color
     * @throws GameCreationException
     * @return ModelGame
     * @throws JoinUserException
     */
    public function create($game_name, $players, $password1, $password2, $creator_joins, $id_color) {
        $id_color = intval($id_color);
        $players = intval($players);

        if (empty($game_name) || empty($players)) {
            throw new GameCreationException('Fill in name and players.');
        }
        if (!preg_match("/^([a-zA-Z0-9]+[a-zA-Z0-9' -]+[a-zA-Z0-9']+)?$/", $game_name)) {
            throw new GameCreationException('Invalid name, only use those letters: a-Z 0-9 \'-');
        }
        if (!preg_match("/[2-6]{1}/", $players)) {
            throw new GameCreationException('Invalid number of players.');
        }
        if ($password1 !== $password2) {
            throw new GameCreationException('Passwords have to match.');
        }
        if (!preg_match("/^([a-zA-Z0-9$%'-]{5,})?$/", $password1)) {
            throw new GameCreationException('Invalid password. At least 5 of the following letters: a-Z 0-9 $%\'-');
        }

        $game = ModelGame::createGame($game_name, $players, ModelUser::getCurrentUser()->getId(), $password1);

        // join user
        if ($creator_joins) {
            ModelIsInGameInfo::joinGame(ModelUser::getCurrentUser()->getId(), $game->getId(), $id_color);
        }

        return $game;
    }

    /**
     * kicks the user out of the game (that was given with the constructor)
     *
     * @param int $id_user
     * @param int $id_game
     * @return bool - true if user was kicked
     * @throws GameAdministrationException
     */
    public function kickUser($id_user, $id_game) {
        $id_game = intval($id_game);
        $id_user = intval($id_user);
        if (!$this->checkMod() && !$this->checkCreator($id_game)) {
            throw new GameAdministrationException('User not allowed to kick player.');
        }
        if (ModelGame::getGame($id_game)->getStatus() !== GAME_STATUS_NEW) {
            throw new GameAdministrationException('Users can only be removed from new games.');
        }

        ModelIsInGameInfo::leaveGame($id_user, $id_game);
        return true;
    }

    /**
     * changes the game password, leave both params empty to set game to no password
     *
     * @param string $password1
     * @param string $password2
     * @param int $id_game
     * @return bool - true if password was changed
     * @throws GameAdministrationException - if passwords don't match or are invalid
     */
    public function changePassword($password1 = null, $password2 = null, $id_game) {
        $id_game = intval($id_game);
        if (!$this->checkMod() && !$this->checkCreator($id_game)) {
            throw new GameAdministrationException('User not allowed to change password.');
        }
        if ($password1 !== $password2) {
            throw new GameAdministrationException('Passwords have to match.');
        }
        if (!empty($password1) && !preg_match("/^([a-zA-Z0-9$%'-]{5,})?$/", $password1)) {
            throw new GameAdministrationException('Invalid password. At least 5 of the following letters: a-Z 0-9 $%\'-');
        }
        $password = ($password1 === null || empty($password1)) ? null : $password1;
        ModelGame::getGame($id_game)->setPassword($password);
        return true;
    }

    /**
     * @param int $id_game
     * @return bool - true on success
     * @throws GameAdministrationException
     */
    public function deleteGame($id_game) {
        $id_game = intval($id_game);
        if (!$this->checkMod() && !$this->checkCreator($id_game)) {
            throw new GameAdministrationException('User not allowed to delete game.');
        }
        ModelGame::deleteGame($id_game);
        return true;
    }

    /**
     * @param int $id_game
     * @return bool
     * @throws GameAdministrationException
     */
    public function startGame($id_game) {
        $id_game = intval($id_game);
        if (!$this->checkMod() && !$this->checkCreator($id_game)) {
            throw new GameAdministrationException('User not allowed to start game.');
        }
        $game = ModelGame::getGame($id_game);
        if ($game->getNumberOfPlayers() < 2) {
            throw new GameAdministrationException('At least 2 players needed to start a game.');
        }

        $game->startGame();
        return true;
    }

    /**
     * sets the game status (and if necessary also changes the phase)
     *
     * @param int $id_game
     * @param string $status - ENUM(new, started, running, done)
     * @return void
     * @throws GameAdministrationException
     */
    public function setStatus($id_game, $status) {
        $id_game = intval($id_game);
        if (!$this->checkMod()) {
            throw new GameAdministrationException('User not allowed to set game status.');
        }
        ModelGame::getGame($id_game)->setStatus($status);
    }

}
