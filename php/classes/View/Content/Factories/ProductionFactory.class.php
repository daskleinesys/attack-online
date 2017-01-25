<?php
namespace Attack\View\Content\Factories;

use Attack\View\Content\Operations;

class ProductionFactory extends Interfaces\ContentFactory {

    public function getName() {
        return 'production';
    }

    public function getOperation() {
        $this->checkAuth(CHECK_SESSION_GAME_RUNNING);
        $return = new Operations\ContentProduction();
        return $return;
    }

}
