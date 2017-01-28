<?php
namespace Attack\GameLogic\Factories;

use Attack\GameLogic\Factories\Interfaces\LogicFactoryInterface;
use Attack\GameLogic\Operations\LogicLandMove;

class LandMoveLogicFactory implements LogicFactoryInterface {

	public function getIdPhase() {
		return PHASE_LANDMOVE;
	}

	public function getOperation($id_game) {
		return new LogicLandMove($id_game);
	}

}
