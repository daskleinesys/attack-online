<?php
namespace AttOn\View\Content\Factories;
use AttOn\View\Content\Operations;

class JoinGameFactory extends Interfaces\ContentFactory {

    public function getName() {
        return 'joingame';
    }

    public function getOperation() {
        $this->checkAuth(CHECK_SESSION_USER);
        $return = new Operations\ContentJoinGame();
        return $return;
    }

}
