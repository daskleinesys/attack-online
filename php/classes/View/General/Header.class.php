<?php
class Header {
	private $id_user;
	private $id_game;
	private $xtpl;

	public function __construct($id_user,$id_game) {
		$this->id_user = $id_user;
		$this->id_game = $id_game;
	}

	public function run() {
		$this->xtpl = new XTemplate('./xtpl/general/header.xtpl');
		
		// vars for template
		$page_title = PAGE_TITLE;
		$this->xtpl->assign('page_title',$page_title);

		// parse head
		$this->xtpl->parse('main.head');

		//show logout frame
		if ($this->id_user != null) $this->loggedIn();

		// show login frame
		else $this->notLoggedIn();


		$this->xtpl->parse('main');
		$this->xtpl->out('main');

	}

	public function loggedIn() {
		$_User = Modeluser::getUser($this->id_user);
		// show inactive frame
		if ($_User->getStatus() == 'inactive') {
			$this->xtpl->parse('main.loginframe.logout.inactive');
		}

		$iter_games = ModelGame::iterator(GAME_STATUS_ALL,$this->id_user);
		// show list of games
		if ($iter_games->hasNext()) {
			if ($this->id_game == null) {
				$this->xtpl->parse('main.loginframe.logout.option.select');
			}

			while ($iter_games->hasNext()) {
				$_Game = $iter_games->next();
				if (($_Game->getStatus() == GAME_STATUS_NEW) || ($_Game->getStatus() == GAME_STATUS_DONE)) continue;
				if ($this->id_game == null) $this->xtpl->assign('selected','');
				else {
					if ($_Game == ModelGame::getGame($this->id_game)) {
						$selected = 'selected="selected"';
						$this->xtpl->assign('selected',$selected);
					} else $this->xtpl->assign('selected','');
				}
				$game_info = array();
				$game_info['id'] = $_Game->getId();
				$game_info['name'] = $_Game->getName();
				$this->xtpl->assign('games',$game_info);
				$this->xtpl->parse('main.loginframe.logout.option.games');
			}
			$this->xtpl->parse('main.loginframe.logout.option');
		}
		$this->xtpl->assign('user_name',$_User->getLogin());
		$this->xtpl->parse('main.loginframe.logout');
		$this->xtpl->parse('main.loginframe');
	}

	private function notLoggedIn() {
		if (!empty($_POST['username'])) {
			$user_username = preg_replace('%(")*(.[^"]{1,40})%','$2',$_POST['username']);
			$this->xtpl->assign('user_username',$user_username);
		}
		else {
			$this->xtpl->assign('user_username','Username');
		}
		$this->xtpl->parse('main.loginframe.login');
		$this->xtpl->parse('main.loginframe');

	}
}
?>