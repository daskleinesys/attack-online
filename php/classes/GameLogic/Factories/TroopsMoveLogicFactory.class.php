<?php
namespace Attack\GameLogic\Factories;

use Attack\GameLogic\Factories\Interfaces\LogicFactoryInterface;
use Attack\GameLogic\Operations\LogicTroopsMove;

class TroopsMoveLogicFactory implements LogicFactoryInterface {

    public function getIdPhase() {
        return PHASE_TROOPSMOVE;
    }

    public function getOperation($id_game) {
        return new LogicTroopsMove($id_game);
    }

}
