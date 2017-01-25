<?php
namespace Attack\View\Content\Factories;

use Attack\View\Content\Operations;

class NewGameFactory extends Interfaces\ContentFactory {

    public function getName() {
        return 'newgame';
    }

    public function getOperation() {
        $this->checkAuth(CHECK_SESSION_USER);
        $return = new Operations\ContentNewGame();
        return $return;
    }

}
