<?php
namespace Attack\View\Content\Factories;

use Attack\View\Content\Operations;

class TroopsMoveFactory extends Interfaces\ContentFactory {

    public function getName() {
        return 'troopsmove';
    }

    public function getOperation() {
        $this->checkAuth(CHECK_SESSION_GAME_RUNNING);
        $return = new Operations\ContentTroopsMove();
        return $return;
    }

}
