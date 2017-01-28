<?php
namespace Attack\View\Content\Operations;

use Attack\Controller\Game\GamesModeration;
use Attack\Exceptions\GameCreationException;
use Attack\Exceptions\JoinUserException;
use Attack\Model\User\ModelColor;
use Attack\Model\User\ModelUser;

class ContentNewGame extends Interfaces\ContentOperation {

    public function getTemplate() {
        return 'newgame';
    }

    public function run(array &$data) {
        $data['template'] = $this->getTemplate();

        if (isset($_POST['create'])) {
            try {
                $this->createGame($data);
                return;
            } catch (GameCreationException $ex) {
                $data['errors'] = array(
                    'message' => $ex->getMessage()
                );
            } catch (JoinUserException $ex) {
                $data['errors'] = array(
                    'message' => 'Game created but unable to join game.'
                );
                return;
            }
        }

        $this->parseCreationForm($data);
    }

    private function parseCreationForm(array &$data) {
        $game = array();
        if (isset($_POST['name']) && preg_match("/^([a-zA-Z0-9]+[a-zA-Z0-9' -]+[a-zA-Z0-9']+)?$/", $_POST['name'])) {
            $game['name'] = $_POST['name'];
        }
        if (isset($_POST['players']) && preg_match("/[2-6]{1}/", $_POST['players'])) {
            $game['players'] = $_POST['players'];
        }
        $data['game'] = $game;

        // colors
        $colors = array();
        $iter = ModelColor::iterator();
        while ($iter->hasNext()) {
            $_Color = $iter->next();
            $color = array();
            $color['id'] = $_Color->getId();
            $color['name'] = $_Color->getName();
            $color['color'] = $_Color->getColor();
            $colors[] = $color;
        }
        $data['colors'] = $colors;
        return $data;
    }

    private function createGame(array &$data) {
        if (!isset($_POST['name']) || empty($_POST['name'])) {
            throw new GameCreationException('Missing game name.');
        }
        if (!isset($_POST['players'])) {
            throw new GameCreationException('Missing players.');
        }
        if (!isset($_POST['password1'])) {
            $_POST['password1'] = '';
        }
        if (!isset($_POST['password2'])) {
            $_POST['password2'] = '';
        }
        if (!isset($_POST['color'])) {
            throw new GameCreationException('Missing color.');
        }
        $creator_joins = (isset($_POST['play']));

        $gamesModeration = new GamesModeration(ModelUser::getCurrentUser()->getId());
        $gamesModeration->create($_POST['name'], $_POST['players'], $_POST['password1'], $_POST['password2'], $creator_joins, $_POST['color']);
        $data['status'] = array(
            'message' => 'Game successfully created.'
        );
        return $data;
    }

}
