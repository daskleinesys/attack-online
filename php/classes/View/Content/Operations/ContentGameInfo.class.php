<?php
namespace AttOn\View\Content\Operations;

use AttOn\Controller\Game\GamesModeration;
use AttOn\Model\Game\ModelGame;
use AttOn\Model\User\ModelUser;
use AttOn\Model\User\ModelIsInGameInfo;
use AttOn\Exceptions\NullPointerException;
use AttOn\Exceptions\GameAdministrationException;

class ContentGameInfo extends Interfaces\ContentOperation {

    /**
     * @var $id_game int
     */
    private $id_game;

    /**
     * @var $game ModelGame
     */
    private $game;

    public function getTemplate() {
        return 'gameinfo';
    }

    /**
     * @param array $data
     * @return void
     */
    public function run(array &$data) {
        $data['template'] = $this->getTemplate();

        $this->id_game = intval($data['id_game']);

        try {
            $this->game = ModelGame::getGame($this->id_game);
        } catch (NullPointerException $ex) {
            $data['errors'] = array(
                'message' => 'Game not found!'
            );
            return;
        }

        if (isset($_POST['creator_action'])) {
            try {
                $this->do_creator_action($data);
            } catch (GameAdministrationException $ex) {
                $data['errors'] = array(
                    'message' => $ex->getMessage()
                );
                return;
            }
        }

        $this->showGame($data);
    }

    private function showGame(array &$data) {
        $gameInfo = array();
        $gameInfo['name'] = $this->game->getName();
        $gameInfo['creator'] = $this->game->getCreator()->getLogin();
        $gameInfo['id'] = $this->game->getId();
        $gameInfo['status'] = $this->game->getStatus();
        $data['game'] = $gameInfo;

        if (isset($_POST['delete'])) {
            $data['delete'] = true;
        }

        if (ModelUser::getCurrentUser() === $this->game->getCreator()) {
            $data['isCreator'] = true;
        }

        $players = array();
        $iter = ModelUser::iterator(STATUS_USER_ALL, $this->game->getId());
        while ($iter->hasNext()) {
            $user = $iter->next();
            $player = array();
            $player['login'] = $user->getLogin();
            $player['color'] = ModelIsInGameInfo::getIsInGameInfo($user->getId(), $this->game->getId())->getColor()->getName();
            $player['id'] = $user->getUserId();
            $players[] = $player;
        }
        $data['game']['player'] = $players;
    }

    private function do_creator_action(array &$data) {
        $gamesModeration = new GamesModeration(ModelUser::getCurrentUser()->getId());

        if (isset($_POST['kick'])) {
            $gamesModeration->kickUser(intval($_POST['kick']), $this->id_game);
            $data['status'] = array(
                'message' => 'User successfully kicked.'
            );
            return;
        }
        if (isset($_POST['change_pw'])) {
            $gamesModeration->changePassword($_POST['password1'], $_POST['password2'], $this->id_game);
            $data['status'] = array(
                'message' => 'Password successfully changed.'
            );
            return;
        }
        if (isset($_POST['start'])) {
            $gamesModeration->startGame($this->id_game);
            $data['status'] = array(
                'message' => 'Game successfully started.'
            );
            return;
        }
        if (isset($_POST['delete_affirmed'])) {
            $gamesModeration->deleteGame($this->id_game);
            $data['status'] = array(
                'message' => 'Game successfully deleted.'
            );
            return;
        }
    }

}
