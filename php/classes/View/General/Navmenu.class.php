<?php
class Navmenu {

	private $id_user;
	private $id_game;
	private $xtpl;

	public function __construct($id_user,$id_game) {
		$this->xtpl = new XTemplate('./xtpl/general/navmenu.xtpl');
		$this->id_user = $id_user;
		$this->id_game = $id_game;
	}

	public function run() {
		$this->ParseMenu();
		
		$x = 0;
		$navbonus = 25 * $x;
		$globalheight = 436 + $navbonus;
		$this->xtpl->assign('globalheight',$globalheight . 'px;');

		$this->xtpl->parse('main');
		$this->xtpl->out('main');
	}

	private function ParseMenu() {
		if ($this->id_user == null) {
			$this->xtpl->parse('main.inactive');
			return;
		}
		$_User = ModelUser::getUser($this->id_user);
		if ($_User->getStatus() == 'inactive') {
			$this->xtpl->parse('main.inactive');
			return;
		}

		if ($this->id_game != null) {
			$_Game = ModelGame::getGame($this->id_game);

			if (($_Game->getStatus() == GAME_STATUS_RUNNING) || ($_Game->getStatus() == GAME_STATUS_STARTED)) {
				$phases = ModelGameMode::getGameMode($_Game->getIdGameMode())->getPhases();
				// check for phases
				foreach ($phases as $phase) {
					switch ($phase) {
						case PHASE_LANDMOVE:
							$this->xtpl->parse('main.active.ingame.landmove');
							break;
						case PHASE_SEAMOVE:
							$this->xtpl->parse('main.active.ingame.seamove');
							$this->xtpl->parse('main.active.started.setships');
							break;
						case PHASE_TRADEROUTE:
							$this->xtpl->parse('main.active.ingame.traderoute');
							break;
						case PHASE_TROOPSMOVE:
							$this->xtpl->parse('main.active.ingame.troopsmove');
							break;
						case PHASE_PRODUCTION:
							$this->xtpl->parse('main.active.ingame.production');
							break;
					}
				}
			}

			// game is running
			if ($_Game->getStatus() == GAME_STATUS_RUNNING) {
				$this->xtpl->parse('main.active.ingame');
			}

			// game has been started recently
			if ($_Game->getStatus() == GAME_STATUS_STARTED) {
				$this->xtpl->parse('main.active.started');
			}
		}
		
		if ($_User->getStatus() == 'moderator') {
			$this->xtpl->parse('main.active.moderator');
		}
		if ($_User->getStatus() == 'admin') {
			$this->xtpl->parse('main.active.moderator');
			$this->xtpl->parse('main.active.admin');
		}

		$this->xtpl->parse('main.active');
	}
}
?>