<?php
namespace Attack\View\Content\Factories;

use Attack\View\Content\Operations\ContentSeaMove;

class SeaMoveFactory extends Interfaces\ContentFactory {

    public function getName() {
        return 'seamove';
    }

    public function getOperation() {
        $this->checkAuth(CHECK_SESSION_GAME_RUNNING);
        $return = new ContentSeaMove();
        return $return;
    }

}
