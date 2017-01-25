<?php
namespace Attack\View\Content\Factories;

use Attack\View\Content\Operations;

class GameInfoFactory extends Interfaces\ContentFactory {

    public function getName() {
        return 'gameinfo';
    }

    public function getOperation() {
        $this->checkAuth(CHECK_SESSION_USER);
        $return = new Operations\ContentGameInfo();
        return $return;
    }

}
