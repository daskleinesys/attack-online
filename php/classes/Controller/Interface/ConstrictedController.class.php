<?php

abstract class ConstrictedController {
	protected $id_user; // user that tries to run these action
	
	/**
	 * @param int $id_user - id of the user accessing the moderation actions
	 */
	public function __construct($id_user) {
		$this->id_user = intval($id_user);
	}
	
	protected function checkAdmin() {
		$_User = ModelUser::getUser($this->id_user);
		if ($_User->getStatus() == STATUS_USER_ADMIN) return true;
		return false;
	}
	
	protected function checkMod() {
		$_User = ModelUser::getUser($this->id_user);
		if ($_User->getStatus() == STATUS_USER_MODERATOR) return true;
		if ($_User->getStatus() == STATUS_USER_ADMIN) return true;
		return false;
	}
	
	protected function checkCreator($id_game) {
		if (ModelGame::getGame($id_game)->getCreator() == ModelUser::getUser($this->id_user)) return true;
		return false;
	}
	
	protected function checkInGame($id_game) {
		if (ModelIsInGameInfo::isUserInGame($this->id_user, $id_game)) return true;
		return false;
	}
}

?>