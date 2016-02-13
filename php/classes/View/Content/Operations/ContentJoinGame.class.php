<?php
namespace AttOn\View\Content\Operations;

class ContentJoinGame extends ContentOperation {

    public function run() {
        $showgame = true;
        if (isset($_POST['join'])) {
            if ($this->joinGame()) $showgame = false;
        }

        if ($showgame) $this->showGame();
        else $this->showContentInfo('Successfully joined game.');
        $this->parseMain();
        return true;
    }

    private function joinGame() {
        $_UserGameInteraction = new UserGameInteraction($this->id_user_logged_in);
        $password = (isset($_POST['password'])) ? $_POST['password'] : null;
        $id_color = (isset($_POST['color'])) ? intval($_POST['color']) : null;
        if (!isset($_POST['id_game'])) {
            $this->showContentError('Missing POST-parameter.');
            return false;
        }

        try {
            $_UserGameInteraction->join($id_color, intval($_POST['id_game']), $password);
        } catch (JoinUserException $ex) {
            $this->showContentError($ex->getMessage());
            return false;
        }
        return true;
    }

    private function showGame() {
        if (!isset($_POST['id_game'])) {
            $this->showContentError('Missing POST-parameters.');
            return false;
        }
        $id_game = intval($_POST['id_game']);
        try {
            $_Game = ModelGame::getGame($id_game);
        } catch (NullPointerException $ex) {
            $this->showContentError('Game not found.');
            return false;
        }

        $gameinfo = array();
        $gameinfo['name'] = $_Game->getName();
        $gameinfo['creator'] = $_Game->getCreator()->getLogin();
        $gameinfo['id'] = $_Game->getId();
        $this->xtpl->assign('gameinfo',$gameinfo);

        $iter_players = ModelUser::iterator(STATUS_USER_ALL,$id_game);
        while ($iter_players->hasNext()) {
            $this->xtpl->assign('player_login',$iter_players->next()->getLogin());
            $this->xtpl->parse('main.game.player');
        }

        foreach ($_Game->getFreeColors() as $color) {
            $this->xtpl->assign('color',$color);
            $this->xtpl->parse('main.game.color');
        }

        if ($_Game->checkPasswordProtection()) $this->xtpl->parse('main.game.password');
        $this->xtpl->parse('main.game');
    }

    private function parseMain() {
        $this->xtpl->parse('main');
        $this->xtpl->out('main');
        return true;
    }

}
