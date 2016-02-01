<?php
namespace AttOn\View\Content\Operations;
use AttOn\Model;
use AttOn\Controller\User\UserGameInteraction;

class ContentGames extends Interfaces\ContentOperation {

	private $game_status;

    public function getTemplate() {
        return 'games';
    }

	public function run(array &$data) {

        // TODO : make ContentGames work
        echo 'TODO : make ContentGames work';
        /*
		if (isset($_POST['leave'])) {
            $this->leaveGame();
        }

		$this->loadGames();
		$iter_games = Model\Game\ModelGame::iterator($this->game_status);

		$this->showStatusBar();
		$this->parseGames($iter_games);
        */

        $data['template'] = $this->getTemplate();
		return $data;
	}

	private function leaveGame() {
		$_UserGameInteraction = new UserGameInteraction($this->id_user_logged_in);
		try {
			$_UserGameInteraction->leaveGame(intval($_POST['leave']));
			$this->showContentInfo('Game successfully left.');
		} catch (ControllerException $ex) {
			$this->showContentError($ex->getMessage());
		}
	}

	private function loadGames() {
		if (!isset($_GET['show'])) {
			$this->game_status = GAME_STATUS_NEW;
			return;
		}
		switch ($_GET['show']) {
			case GAME_STATUS_NEW:
				$this->game_status = GAME_STATUS_NEW;
				break;
			case GAME_STATUS_DONE:
				$this->game_status = GAME_STATUS_DONE;
				break;
			case GAME_STATUS_RUNNING:
				$this->game_status = GAME_STATUS_RUNNING;
				break;
			default:
				$this->game_status = GAME_STATUS_NEW;
				break;
		}
	}

	private function showStatusBar() {
		if (!isset($_GET['show'])) {
			$this->xtpl->parse('main.status1');
			return;
		}
		switch ($_GET['show']) {
			case GAME_STATUS_NEW:
				$this->xtpl->parse('main.status1');
				break;
			case GAME_STATUS_DONE:
				$this->xtpl->parse('main.status3');
				break;
			case GAME_STATUS_RUNNING:
				$this->xtpl->parse('main.status2');
				break;
			default:
				$this->xtpl->parse('main.status1');
				break;
		}
	}

	private function parseGames($iter_games) {
		while ($iter_games->hasNext()) {
			$_Game = $iter_games->next();
			$game_info = array();
			$game_info['id'] = $_Game->getId();
			$game_info['name'] = $_Game->getName();
			$game_info['slots'] = $_Game->getPlayerSlots();
			$game_info['free_slots'] = $_Game->getFreeSlots();
			$game_info['creator'] = $_Game->getCreator()->getLogin();
			$this->xtpl->assign('game',$game_info);
			if ($_Game->checkPasswordProtection()) $this->xtpl->parse('main.' . $this->game_status . '.game.password');

			// user is ingame/creator
			$ingame = '';
			if (ModelUser::getUser($this->id_user_logged_in) == $_Game->getCreator()) $ingame = 'creator_';
			if (ModelIsInGameInfo::isUserInGame($this->id_user_logged_in,$_Game->getId())) {
				$ingame .= 'ingame';
				$this->xtpl->parse('main.' . $this->game_status . '.game.joined');
			} else $ingame .= 'notingame';

			$this->xtpl->assign('ingame',$ingame);


			$this->xtpl->parse('main.' . $this->game_status . '.game');
			$this->xtpl->parse('main.' . $this->game_status . '.gamelist');
			if (($this->game_status == GAME_STATUS_NEW) && ($_Game->getFreeSlots() > 0)) {
				$this->xtpl->parse('main.' . $this->game_status . '.join');
			}
		}
	}

}
