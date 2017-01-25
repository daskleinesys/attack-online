<?php
namespace Attack\View\Content\Factories;

use Attack\View\Content\Operations;

class LandMoveFactory extends Interfaces\ContentFactory {

    public function getName() {
        return 'landmove';
    }

    public function getOperation() {
        $this->checkAuth(CHECK_SESSION_GAME_RUNNING);
        $return = new Operations\ContentLandMove();
        return $return;
    }

}
