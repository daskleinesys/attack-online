<?php
namespace Attack\GameLogic\Factories;

use Attack\GameLogic\Factories\Interfaces\LogicFactoryInterface;
use Attack\GameLogic\Operations\LogicSetShips;

class SetShipsLogicFactory implements LogicFactoryInterface {

    public function getIdPhase() {
        return PHASE_SETSHIPS;
    }

    public function getOperation($id_game) {
        return new LogicSetShips($id_game);
    }

}