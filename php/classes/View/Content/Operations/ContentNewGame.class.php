<?php
class ContentNewGame extends ContentOperation {

	public function run() {
		if (isset($_POST['create'])) {
			try {
				$this->createGame();
				$this->showContentInfo('Game successfully created.');
				$this->parseMain();
				return true;
			} catch (GameCreationException $ex) {
				$this->showContentError($ex->getMessage());
			}
		}

		$this->parseCreationForm();
		$this->parseMain();
	}

	private function parseMain() {
		$this->xtpl->parse('main');
		$this->xtpl->out('main');
		return true;
	}

	private function parseCreationForm() {
		if (isset($_POST['name'])) if (preg_match("/^([a-zA-Z0-9]+[a-zA-Z0-9' -]+[a-zA-Z0-9']+)?$/",$_POST['name']))
		$this->xtpl->assign('game_name',$_POST['name']);
		if (isset($_POST['players'])) if (preg_match("/[2-6]{1}/",$_POST['players']))
		$this->xtpl->assign('game_players',$_POST['players']);

		// game modes
		// dict(id => int, name => string, phases => array(ints), abbreviation => string))
		$iter = ModelGameMode::iterator();
		while ($iter->hasNext()) {
			$_GameMode = $iter->next();
			$game_mode = array();
			$game_mode['id'] = $_GameMode->getId();
			$game_mode['name'] = $_GameMode->getName();
			$game_mode['abbreviation'] = $_GameMode->getAbbreviation();
			$this->xtpl->assign('game_mode',$game_mode);
			$this->xtpl->parse('main.newgame.game_mode');
		}
		// colors
		$iter = ModelColor::iterator();
		while ($iter->hasNext()) {
			$_Color = $iter->next();
			$color = array();
			$color['id'] = $_Color->getId();
			$color['name'] = $_Color->getName();
			$color['color'] = $_Color->getColor();
			$this->xtpl->assign('color',$color);
			$this->xtpl->parse('main.newgame.color');
		}
		$this->xtpl->parse('main.newgame');
	}

	private function createGame() {
		if (!isset($_POST['name'])) throw new GameCreationException('Missing POST-parameters.');
		if (!isset($_POST['players'])) throw new GameCreationException('Missing POST-parameters.');
		if (!isset($_POST['password1'])) throw new GameCreationException('Missing POST-parameters.');
		if (!isset($_POST['password2'])) throw new GameCreationException('Missing POST-parameters.');
		if (!isset($_POST['game_mode'])) throw new GameCreationException('Missing POST-parameters.');
		if (!isset($_POST['color'])) throw new GameCreationException('Missing POST-parameters.');
		if (isset($_POST['play'])) $creator_joins = true;
		else $creator_joins = false;
		
		$_GamesModeration = new GamesModeration($this->id_user_logged_in);
		$id_game = $_GamesModeration->create($_POST['name'], $_POST['players'], $_POST['password1'], $_POST['password2'], $_POST['game_mode'], $creator_joins, $_POST['color']);
	}
}
?>