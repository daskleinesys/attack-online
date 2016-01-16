<?php

class UserGameInteraction extends ConstrictedController {
	private $_Logger;
	
	/**
	 * @param int $id_user - id of the user accessing the moderation actions
	 */
	public function __construct($id_user) {
		parent::__construct($id_user);
		$this->_Logger = Logger::getLogger('UserGameInteraction');
	}
	
	/**
	 * tries to join a game with the user
	 * @param int $id_color
	 * @param int $id_game
	 * @param string $password
	 * @throws JoinUserException,NullPointerException
	 */
	public function join($id_color = null, $id_game, $password = null) {
		$id_color = intval($id_color);
		$id_game = intval($id_game);
		
		$_Game = ModelGame::getGame($id_game);
		
		// check password
		if ($_Game->checkPasswordProtection()) if (!$_Game->checkPassword($password)) throw new JoinUserException('Invalid password.');
		if (ModelIsInGameInfo::isUserInGame($this->id_user, $id_game)) throw new JoinUserException('User already in this game!');
		if ($_Game->getFreeSlots() <= 0) throw new JoinUserException('Game is full.');
		
		// join game
		ModelIsInGameInfo::joinGame($this->id_user, $id_game, $id_color);
	}

	/**
	 * trys to leave the given game, returns true on success
	 * @param int $id_game
	 * @throws ControllerException
	 * @return boolean
	 */
	public function leaveGame($id_game) {
		$id_game = intval($id_game);
		try {
			$_Game = ModelGame::getGame($id_game);
		} catch (NullPointerException $ex) {
			throw new ControllerException('Game not found.');
		}
		
		if ($_Game->getStatus() != 'new') throw new ControllerException('Can\'t leave game. It has allready started.');
		if ($_Game->checkProcessing()) throw new ControllerException('Can\'t leave game. It has allready started.');
		
		ModelIsInGameInfo::leaveGame($id_game, $this->id_user);
		return true;
	}
	
	/**
	 * selects a game and throws exception if game is not found
	 * @param int $id_game
	 * @throws NullPointerException, ControllerException
	 * @return int id_game
	 */
	public function selectGame($id_game) {
		$id_game = intval($id_game);
		if (!$this->checkInGame($id_game)) throw new ControllerException('Unable to select game as the user is not in it.');
		$_Game = ModelGame::getGame($id_game);
		if ($_Game->getStatus() == GAME_STATUS_NEW) throw new ControllerException('Unable to select game as it is not yet started.');
		if ($_Game->getStatus() == GAME_STATUS_DONE) throw new ControllerException('Unable to select game as it is already done.');
		
		$_SESSION['game_id'] = $id_game;
		return $_Game->getId();
	}
}

?>