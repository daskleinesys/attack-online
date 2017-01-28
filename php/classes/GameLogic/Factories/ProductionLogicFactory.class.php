<?php
namespace Attack\GameLogic\Factories;

use Attack\GameLogic\Factories\Interfaces\LogicFactoryInterface;
use Attack\GameLogic\Operations\LogicProduction;

class ProductionLogicFactory implements LogicFactoryInterface {

    public function getIdPhase() {
        return PHASE_PRODUCTION;
    }

    public function getOperation($id_game) {
        return new LogicProduction($id_game);
    }

}
