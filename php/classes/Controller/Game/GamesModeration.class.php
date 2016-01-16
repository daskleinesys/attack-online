<?php

class GamesModeration extends ConstrictedController {
	private $_Logger;
	
	/**
	 * @param int $id_user - id of the user accessing the moderation actions
	 */
	public function __construct($id_user) {
		parent::__construct($id_user);
		$this->_Logger = Logger::getLogger('GamesModeration');
	}
	
	/**
	 * try to create a new game, returns the game as model on success
	 * @param string $game_name
	 * @param int $players
	 * @param string $password1
	 * @param string $password2
	 * @param int $game_mode
	 * @param bool $creator_joins
	 * @param int $id_color
	 * @throws GameCreationException
	 * @throws Exception
	 * @return ModelGame
	 */
	public function create($game_name,$players,$password1,$password2,$game_mode,$creator_joins,$id_color) {
		$id_color = intval($id_color);
		$players = intval($players);

		if (empty($game_name) || empty($players)) throw new GameCreationException('Fill in name and players.');
		if (!preg_match("/^([a-zA-Z0-9]+[a-zA-Z0-9' -]+[a-zA-Z0-9']+)?$/",$game_name))
		throw new GameCreationException('Invalid name, only use those letters: a-Z 0-9 \'-');
		if (!preg_match("/[2-6]{1}/",$players)) throw new GameCreationException('Invalid number of players.');

		if ($password1 != $password2) throw new Exception('Passwords have to match.');
		if (!preg_match("/^([a-zA-Z0-9$%'-]{5,})?$/",$password1)) throw new GameCreationException('Invalid password. At least 5 of the following letters: a-Z 0-9 $%\'-');
		
		$_Game = ModelGame::createGame($game_name, $game_mode, $players, $this->id_user, $password1);
		
		if (!$creator_joins) return $_Game->getId();
		
		// join user
		try {
			ModelIsInGameInfo::joinGame($this->id_user,$_Game->getid(),$id_color);
		} catch (JoinUserException $ex) {
		}
		
		return $_Game->getId();
	}
	
	/**
	 * kicks the user out of the game (that was given with the constructor)
	 * @param int $id_user
	 * @param int $id_game
	 * @throws GameAdministrationException
	 * @return bool - true if user was kicked
	 */
	public function kickUser($id_user, $id_game) {
		$id_game = intval($id_game);
		$id_user = intval($id_user);
		if (!$this->checkMod() && !$this->checkCreator($id_game)) throw new GameAdministrationException('User not allowed to use these actions.');
		if (ModelGame::getGame($id_game)->getStatus() != GAME_STATUS_NEW) throw new GameAdministrationException('Users can only be removed from new games.');
		
		ModelIsInGameInfo::leaveGame($id_game, $id_user);
		return true;
	}
	
	/**
	 * changes the game password, leave both params empty to set game to no password
	 * @param string $password1
	 * @param string $password2
	 * @throws GameAdministrationException - if passwords don't match or are invalid
	 * @return bool - true if password was changed
	 */
	public function changePassword($password1 = null,$password2 = null,$id_game) {
		$id_game = intval($id_game);
		if (!$this->checkMod() && !$this->checkCreator($id_game)) throw new GameAdministrationException('User not allowed to use these actions.');
		if ($password1 != $password2) throw new GameAdministrationException('Passwords have to match.');
		if (!empty($password1)) if (!preg_match("/^([a-zA-Z0-9$%'-]{5,})?$/",$password1)) throw new GameAdministrationException('Invalid password. At least 5 of the following letters: a-Z 0-9 $%\'-');
		$password = ($password1 == null || empty($password1)) ? null : $password1;
		ModelGame::getGame($id_game)->setPassword($password);
		return true;
	}

	/**
	 * @throws GameAdministrationException
	 * @return bool - true on success
	 */
	public function deleteGame($id_game) {
		$id_game = intval($id_game);
		if (!$this->checkMod() && !$this->checkCreator($id_game)) throw new GameAdministrationException('User not allowed to use these actions.');
		try {
			ModelGame::deleteGame($id_game);
			return true;
		} catch (GameAdministrationException $ex) {
			$this->_Logger->error($ex);
			throw $ex;
		}
		return false;
	}

	/**
	 * @throws GameAdministrationException
	 * @return bool
	 */
	public function startGame($id_game) {
		$id_game = intval($id_game);
		if (!$this->checkMod() && !$this->checkCreator($id_game)) throw new GameAdministrationException('User not allowed to use these actions.');
		$_Game = ModelGame::getGame($id_game);
		if ($_Game->getNumberOfPlayers() < 2) throw new GameAdministrationException('At least 2 players needed to start a game.');
		try {
			$_Game->startGame();
			return true;
		} catch (GameAdministrationException $ex) {
			$this->_Logger->error($ex);
			throw $ex;
		}
		return false;
	}
	
	/**
	 * sets the game status (and if necessary also changes the phase)
	 * @param enum $status
	 * @throws GameAdministrationException
	 * @return void
	 */
	public function setStatus($id_game,$status) {
		$id_game = intval($id_game);
		if (!$this->checkMod()) throw new GameAdministrationException('User not allowed to use these actions.');
		ModelGame::getGame($id_game)->setStatus($status);
	}
}



?>