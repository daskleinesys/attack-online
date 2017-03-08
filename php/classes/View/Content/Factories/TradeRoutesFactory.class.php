<?php
namespace Attack\View\Content\Factories;

use Attack\View\Content\Operations\ContentTradeRoutes;

class TradeRoutesFactory extends Interfaces\ContentFactory {

    public function getName() {
        return 'traderoutes';
    }

    public function getOperation() {
        $this->checkAuth(CHECK_SESSION_GAME_RUNNING);
        $return = new ContentTradeRoutes();
        return $return;
    }

}
