<?php
namespace Attack\Controller\Logic\Factories;

use Attack\Controller\Logic\Factories\Interfaces\LogicFactoryInterface;
use Attack\Controller\Logic\Operations\LogicProduction;

class ProductionLogicFactory implements LogicFactoryInterface {

    public function getIdPhase() {
        return PHASE_PRODUCTION;
    }

    public function getOperation($id_game) {
        return new LogicProduction($id_game);
    }

}
