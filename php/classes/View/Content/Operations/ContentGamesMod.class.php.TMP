<?php
class ContentGamesMod extends ContentOperation {

	public function run() {
		
		try {
			if (isset($_POST['gamesmod_action'])) $this->modAction();
		} catch (Exception $ex) {
			$this->showErrorMsg($ex->getMessage());
		}
		
		if (isset($_POST['delete']) || isset($_POST['delete_multiple'])) $this->showAffirmation();
		
		$this->showGames();

		
		$this->xtpl->parse('main');
		$this->xtpl->out('main');
		return true;
	}
	
	private function modAction() {
		$_GamesModeration = new GamesModeration($this->id_user_logged_in);
		
		// change a game
		if (isset($_POST['new'])) $_GamesModeration->setStatus($_POST['new'], GAME_STATUS_NEW);
		if (isset($_POST['running'])) $_GamesModeration->setStatus($_POST['running'], GAME_STATUS_RUNNING);
		if (isset($_POST['done'])) $_GamesModeration->setStatus($_POST['done'], GAME_STATUS_DONE);
		
		// change multiple games
		if (isset($_POST['new_multiple'])) {
			foreach ($_POST['action'] as $id_game) {
				$_GamesModeration->setStatus($id_game, GAME_STATUS_NEW);
			}
		}
		if (isset($_POST['running_multiple'])) {
			foreach ($_POST['action'] as $id_game) {
				$_GamesModeration->setStatus($id_game, GAME_STATUS_RUNNING);
			}
		}
		if (isset($_POST['done_multiple'])) {
			foreach ($_POST['action'] as $id_game) {
				$_GamesModeration->setStatus($id_game, GAME_STATUS_DONE);
			}
		}
		if (isset($_POST['affirmation'])) {
			foreach ($_POST['action'] as $id_game) {
				$_GamesModeration->deleteGame($id_game);
			}
		}
	}

	private function showAffirmation() {
		$gameIds = array();
		if (isset($_POST['delete'])) $gameIds[] = $_POST['delete'];
		if (isset($_POST['delete_multiple'])) $gameIds = $_POST['action'];
		
		foreach ($gameIds as $id_game) {
			$_Game = ModelGame::getGame($id_game);
			$game = array();
			$game['id'] = $_Game->getId();
			$game['name'] = $_Game->getName();
			$game['players'] = $_Game->getNumberOfPlayers();
			$game['maxplayers'] = $_Game->getPlayerSlots();
			$game['creator'] = $_Game->getCreator()->getLogin();
			$game['status'] = $_Game->getStatus();
			$this->xtpl->assign('games',$game);
			$this->xtpl->parse('main.delete.games');
		}
		$this->xtpl->parse('main.delete');
	}
	
	private function showGames() {
		$iter_games = ModelGame::iterator(GAME_STATUS_ALL);
		
		while ($iter_games->hasNext()) {
			$_Game = $iter_games->next();
			$game_info = array();
			$game_info['id'] = $_Game->getId();
			$game_info['name'] = $_Game->getName();
			$game_info['players'] = $_Game->getNumberOfPlayers();
			$game_info['maxplayers'] = $_Game->getPlayerSlots();
			$game_info['creator'] = $_Game->getCreator()->getLogin();
			$game_info['status'] = $_Game->getStatus();
			$game_info['phase'] = $_Game->getIdPhase();
			
			$this->xtpl->assign('games',$game_info);
			$this->xtpl->parse('main.games');
		}
		
	}
}
?>