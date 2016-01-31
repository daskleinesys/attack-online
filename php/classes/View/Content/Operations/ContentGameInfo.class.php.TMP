<?php
class ContentGameInfo extends ContentOperation {

	private $id_game;
	private $_GamesModeration;

	public function run() {

		if (!isset($_POST['games'])) {
			$this->showErrorMsg('No game selected!');
			$this->parseMain();
			return true;
		}

		$this->id_game = intval($_POST['games']);
		
		try {
			ModelGame::getGame($this->id_game);
		} catch (NullPointerException $ex) {
			$this->_Logger->error($ex);
			$this->showErrorMsg('Game not found!');
			$this->parseMain();
			return true;
		}

		if (isset($_POST['creator_action'])) $this->do_creator_action();

		$this->showGame();
		$this->parseMain();
	}

	private function parseMain() {
		$this->xtpl->parse('main');
		$this->xtpl->out('main');
		return true;
	}

	private function showGame() {
		try {
			$_Game = ModelGame::getGame($this->id_game);
		} catch (NullPointerException $ex) {
			return;
		}
		$gameInfo = array();
		$game['name'] = $_Game->getName();
		$game['mode'] = ModelGameMode::getGameMode($_Game->getIdGameMode())->getName();
		$game['creator'] = $_Game->getCreator()->getLogin();
		$game['id'] = $_Game->getId();
		$this->xtpl->assign('game',$game);
		
		if (isset($_POST['delete'])) $this->xtpl->parse('main.game.delete');

		if (ModelUser::getUser($this->id_user_logged_in) == $_Game->getCreator()) if ($_Game->getStatus() == GAME_STATUS_NEW) {
			$this->xtpl->parse('main.game.creator_kick');
			$this->xtpl->parse('main.game.creator_new_game');
		}

		$iter = ModelUser::iterator(STATUS_USER_ALL,$_Game->getId());
		while ($iter->hasNext()) {
			$_User = $iter->next();
			$player = array();
			$player['login'] = $_User->getLogin();
			$player['color'] = ModelIsInGameInfo::getIsInGameInfo($_User->getId(), $_Game->getId())->getColor()->getName();
			$player['id'] = $_User->getUserId();
			$this->xtpl->assign('player',$player);
			if (ModelUser::getUser($this->id_user_logged_in) == $_Game->getCreator()) if ($_Game->getStatus() == GAME_STATUS_NEW) 
			$this->xtpl->parse('main.game.player.creator_kick');
			$this->xtpl->parse('main.game.player');
		}
		$this->xtpl->parse('main.game');
	}

	private function do_creator_action() {
		$this->_GamesModeration = new GamesModeration($this->id_user_logged_in);
		try {
			$this->select_action();
			return true;
		} catch (GameAdministrationException $ex) {
			$this->showContentError($ex->getMessage());
			return false;
		}


	}

	private function select_action() {
		if (isset($_POST['kick'])) {
			$this->_GamesModeration->kickUser(intval($_POST['kick']), $this->id_game);
			$this->showContentInfo('User successfully kicked.');
			return true;
		}
		if (isset($_POST['change_pw'])) {
			$this->_GamesModeration->changePassword($_POST['password1'], $_POST['password2'], $this->id_game);
			$this->showContentInfo('Password successfully changed.');
			return true;
		}
		if (isset($_POST['start'])) {
			$this->_GamesModeration->startGame($this->id_game);
			$this->showContentInfo('Game successfully started.');
			return true;
		}
		if (isset($_POST['delete_affirmed'])) {
			$this->_GamesModeration->deleteGame($this->id_game);
			$this->showContentInfo('Game successfully deleted.');
			return true;
		}
	}
}
?>