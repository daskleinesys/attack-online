<?php
namespace Attack\View\Content\Factories;

use Attack\View\Content\Operations;

class SetShipsFactory extends Interfaces\ContentFactory {

    public function getName() {
        return 'setships';
    }

    public function getOperation() {
        $this->checkAuth(CHECK_SESSION_GAME_START);
        $return = new Operations\ContentSetShips();
        return $return;
    }

}
