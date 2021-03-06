<?php
namespace Attack\View\Content\Operations;

use Attack\Controller\User\UserGameInteraction;
use Attack\Model\Game\ModelGame;
use Attack\Model\User\ModelUser;
use Attack\Model\User\ModelIsInGameInfo;
use Attack\Exceptions\ControllerException;
use Attack\Exceptions\NullPointerException;

class ContentGames extends Interfaces\ContentOperation {

    public function getTemplate() {
        return 'games';
    }

    public function run(array &$data) {
        $data['template'] = $this->getTemplate();
        $data['games'] = array();

        if (isset($_POST['leave']) && isset($_POST['id_game'])) {
            $this->leaveGame($data, intval($_POST['id_game']));
        }

        $this->setStatusBar($data);
        try {
            $iter_games = $this->loadGames($data);
            $this->parseGames($data, $iter_games);
        } catch (NullPointerException $ex) {
            $data['errors'] = array(
                'message' => $ex->getMessage()
            );
        }

        return $data;
    }

    private function leaveGame(array &$data, $id_game) {
        $interaction = new UserGameInteraction();
        try {
            $interaction->leaveGame($id_game);
            $data['status'] = array(
                'message' => 'Game left.'
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

    private function loadGames($data) {
        switch ($data['type']) {
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
                throw new NullPointerException('Invalid Game-Type');
                break;
        }
        return ModelGame::iterator($game_status);
    }

    private function setStatusBar(array &$data) {
        switch ($data['type']) {
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
            $game_info['status'] = $game->getStatus();
            $game_info['slots'] = $game->getPlayerSlots();
            $game_info['free_slots'] = $game->getFreeSlots();
            $game_info['creator'] = $game->getCreator()->getLogin();
            $game_info['password'] = $game->checkPasswordProtection();
            $game_info['created'] = ModelUser::getCurrentUser() === $game->getCreator();
            $game_info['ingame'] = ModelIsInGameInfo::isUserInGame(ModelUser::getCurrentUser()->getId(), $game->getId());
            $data['games']['list'][] = $game_info;
        }
    }

}
