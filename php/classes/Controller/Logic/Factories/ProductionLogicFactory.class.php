<?php
namespace AttOn\Controller\Logic\Factories;

use AttOn\Controller\Logic\Factories\Interfaces\LogicFactoryInterface;
use AttOn\Controller\Logic\Operations\LogicProduction;

class ProductionLogicFactory implements LogicFactoryInterface {

    public function getIdPhase() {
        return PHASE_PRODUCTION;
    }

    public function getOperation($id_game) {
        return new LogicProduction($id_game);
    }

}
