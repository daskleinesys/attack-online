<?php
namespace Attack\GameLogic\Factories;

use Attack\GameLogic\Factories\Interfaces\LogicFactoryInterface;
use Attack\GameLogic\Operations\LogicSeaMove;

class SeaMoveLogicFactory implements LogicFactoryInterface {

    public function getIdPhase() {
        return PHASE_SEAMOVE;
    }

    public function getOperation($id_game) {
        return new LogicSeaMove($id_game);
    }

}