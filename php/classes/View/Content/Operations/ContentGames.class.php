<?php
namespace AttOn\View\Content\Operations;
use AttOn\Model\Game\ModelGame;
use AttOn\Model\User\ModelUser;
use AttOn\Model\User\ModelIsInGameInfo;
use AttOn\Controller\User\UserGameInteraction;
use AttOn\Exceptions\ControllerException;
use AttOn\Exceptions\NullPointerException;

class ContentGames extends Interfaces\ContentOperation {

    public function getTemplate() {
        return 'games';
    }

	public function run(array &$data) {
        $data['games'] = array();

		if (isset($_POST['games']) && is_array($_POST['games']) && isset($_POST['games']['leave'])) {
            $this->leaveGame($data, $_POST['games']['leave']);
        }

		$this->setStatusBar($data);
		$iter_games = $this->loadGames();
		$this->parseGames($data, $iter_games);

        $data['template'] = $this->getTemplate();
		return $data;
	}

	private function leaveGame(array &$data, $id_game) {
		$userGameInteraction = new UserGameInteraction();
		try {
			$userGameInteraction->leaveGame($data, intval($id_game));
            $data['status'] = array(
                'message' => 'Game joined.'
            );
		} catch (ControllerException $ex) {
            $data['errors'] = array(
                'message' => $ex->getMessage()
            );
		} catch (NullPointerException $ex) {
            $data['errors'] = array(
                'message' => 'Game not found'
            );
		}
	}

	private function loadGames() {
		if (!isset($_GET['show'])) {
			$game_status = GAME_STATUS_NEW;
		} else {
            switch ($_GET['show']) {
                case GAME_STATUS_NEW:
                    $game_status = GAME_STATUS_NEW;
                    break;
                case GAME_STATUS_DONE:
                    $game_status = GAME_STATUS_DONE;
                    break;
                case GAME_STATUS_RUNNING:
                    $game_status = GAME_STATUS_RUNNING;
                    break;
                default:
                    $game_status = GAME_STATUS_NEW;
                    break;
            }
        }
		$return = ModelGame::iterator($game_status);
	}

	private function setStatusBar(array &$data) {
		if (!isset($_GET['show'])) {
            $data['games']['show'] = GAME_STATUS_NEW;
			return;
		}
		switch ($_GET['show']) {
			case GAME_STATUS_NEW:
                $data['games']['show'] = GAME_STATUS_NEW;
				break;
			case GAME_STATUS_DONE:
                $data['games']['show'] = GAME_STATUS_DONE;
				break;
			case GAME_STATUS_RUNNING:
                $data['games']['show'] = GAME_STATUS_RUNNING;
				break;
			default:
                $data['games']['show'] = GAME_STATUS_NEW;
				break;
		}
	}

	private function parseGames(array &$data, $iter_games) {
        $data['games']['list'] = array();
		while ($iter_games->hasNext()) {
			$game = $iter_games->next();
			$game_info = array();
			$game_info['id'] = $game->getId();
			$game_info['name'] = $game->getName();
			$game_info['status'] = $this->game_status();
			$game_info['slots'] = $game->getPlayerSlots();
			$game_info['free_slots'] = $game->getFreeSlots();
			$game_info['creator'] = $game->getCreator()->getLogin();
            $game_info['password'] = $game->checkPasswordProtection();
            $game_info['creator'] = ModelUser::getCurrentUser() === $game->getCreator();
            $game_info['ingame'] = ModelIsInGameInfo::isUserInGame(ModelUser::getCurrentUser()->getId(), $game->getId());
            $data['games']['list'][] = $game_info;
		}
        var_dump($data);
        die();
	}

}
