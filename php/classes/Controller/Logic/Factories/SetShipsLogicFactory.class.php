<?php
namespace AttOn\Controller\Logic\Factories;

use AttOn\Controller\Logic\Factories\Interfaces\LogicFactoryInterface;
use AttOn\Controller\Logic\Operations\LogicSetShips;

class SetShipsLogicFactory implements LogicFactoryInterface {

    public function getIdPhase() {
        return PHASE_SETSHIPS;
    }

    public function getOperation($id_game) {
        return new LogicSetShips($id_game);
    }

}