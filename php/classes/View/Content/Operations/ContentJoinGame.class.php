<?php
namespace AttOn\View\Content\Operations;

use AttOn\Controller\User\UserGameInteraction;
use AttOn\Model\Game\ModelGame;
use AttOn\Model\User\ModelUser;
use AttOn\Exceptions\JoinUserException;
use AttOn\Exceptions\NullPointerException;

class ContentJoinGame extends Interfaces\ContentOperation {

    private $id_game;
    private $game;

    public function getTemplate() {
        return 'joingame';
    }

    public function run(array &$data) {
        $data['template'] = $this->getTemplate();

        $this->id_game = intval($data['id_game']);

        try {
            $this->game = ModelGame::getGame($this->id_game);
        } catch (NullPointerException $ex) {
            $data['errors'] = array(
                'message' => 'Game not found!'
            );
            return true;
        }

        if (isset($_POST['join'])) {
            $this->joinGame($data);
        }

        $this->parseGame($data);
    }

    private function joinGame(array &$data) {
        $interaction = new UserGameInteraction(ModelUser::getCurrentUser()->getId());
        $password = (isset($_POST['password'])) ? $_POST['password'] : null;
        $id_color = (isset($_POST['color'])) ? intval($_POST['color']) : null;

        try {
            $interaction->join($id_color, $this->id_game, $password);
            $data['status'] = array(
                'message' => 'Game successfully joined.'
            );
        } catch (JoinUserException $ex) {
            $data['errors'] = array(
                'message' => $ex->getMessage()
            );
        }
    }

    private function parseGame(array &$data) {
        $gameinfo = array();
        $gameinfo['name'] = $this->game->getName();
        $gameinfo['creator'] = $this->game->getCreator()->getLogin();
        $gameinfo['id'] = $this->game->getId();
        $gameinfo['passwordProtected'] = $this->game->checkPasswordProtection();

        $players = array();
        $iter_players = ModelUser::iterator(STATUS_USER_ALL, $this->id_game);
        while ($iter_players->hasNext()) {
            $players[] = $iter_players->next()->getLogin();
        }
        $gameinfo['players'] = $players;

        $gameinfo['availColors'] = $this->game->getFreeColors();

        $data['game'] = $gameinfo;
    }

}
