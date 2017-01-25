<?php
namespace Attack\Controller\Logic\Factories;

use Attack\Controller\Logic\Factories\Interfaces\LogicFactoryInterface;
use Attack\Controller\Logic\Operations\LogicSetShips;

class SetShipsLogicFactory implements LogicFactoryInterface {

    public function getIdPhase() {
        return PHASE_SETSHIPS;
    }

    public function getOperation($id_game) {
        return new LogicSetShips($id_game);
    }

}