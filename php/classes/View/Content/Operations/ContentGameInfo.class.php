<?php
namespace AttOn\View\Content\Operations;
use AttOn\Model\Atton\ModelGameMode;
use AttOn\Model\Game\ModelGame;
use AttOn\Model\User\ModelUser;
use AttOn\Model\User\ModelIsInGameInfo;
use AttOn\Exceptions\NullPointerException;
use AttOn\Exceptions\GameAdministrationException;

class ContentGameInfo extends Interfaces\ContentOperation {

	private $id_game;
	private $game;

    public function getTemplate() {
        return 'gameinfo';
    }

	public function run(array &$data) {
        $data['template'] = $this->getTemplate();

		if (!isset($_POST['id_game'])) {
            $data['errors'] = array(
                'message' => 'No game selected!'
            );
			return true;
		}

		$this->id_game = intval($_POST['id_game']);

		try {
			$this->game = ModelGame::getGame($this->id_game);
		} catch (NullPointerException $ex) {
            $data['errors'] = array(
                'message' => 'Game not found!'
            );
			return true;
		}

		if (isset($_POST['creator_action'])) {
            try {
                $this->do_creator_action($data);
            } catch (GameAdministrationException $ex) {
                $data['errors'] = array(
                    'message' => $ex->getMessage()
                );
                return false;
            }
        }

		$this->showGame($data);
	}

	private function showGame(array &$data) {
		$gameInfo = array();
		$gameInfo['name'] = $this->game->getName();
		$gameInfo['mode'] = ModelGameMode::getGameMode($this->game->getIdGameMode())->getName();
		$gameInfo['creator'] = $this->game->getCreator()->getLogin();
		$gameInfo['id'] = $this->game->getId();
        $gameInfo['status'] = $game->getStatus();
        $data['game'] = $gameInfo;

		if (isset($_POST['delete'])) {
            $data['delete'] = true;
        }

		if (ModelUser::getCurrentUser() === $game->getCreator()) {
            $data['isCreator'] = true;
		}

        $player = array();
		$iter = ModelUser::iterator(STATUS_USER_ALL, $game->getId());
		while ($iter->hasNext()) {
			$user = $iter->next();
			$player['login'] = $user>getLogin();
			$player['color'] = ModelIsInGameInfo::getIsInGameInfo($user->getId(), $game->getId())->getColor()->getName();
			$player['id'] = $user->getUserId();
		}
	}

	private function do_creator_action(array &$data) {
		$gamesModeration = new GamesModeration(ModelUser::getCurrentUser()->getId());

		if (isset($_POST['kick'])) {
			$gamesModeration->kickUser(intval($_POST['kick']), $this->id_game);
            $data['status'] = array(
                'message' => 'User successfully kicked.'
            );
			return true;
		}
		if (isset($_POST['change_pw'])) {
			$gamesModeration->changePassword($_POST['password1'], $_POST['password2'], $this->id_game);
            $data['status'] = array(
                'message' => 'Password successfully changed.'
            );
			return true;
		}
		if (isset($_POST['start'])) {
			$gamesModeration->startGame($this->id_game);
            $data['status'] = array(
                'message' => 'Game successfully started.'
            );
			return true;
		}
		if (isset($_POST['delete_affirmed'])) {
			$gamesModeration->deleteGame($this->id_game);
            $data['status'] = array(
                'message' => 'Game successfully deleted.'
            );
			return true;
		}
	}

}
