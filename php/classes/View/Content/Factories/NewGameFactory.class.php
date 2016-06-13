<?php
namespace AttOn\View\Content\Factories;

use AttOn\View\Content\Operations;

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
