<?php
namespace Attack\Controller\Logic\Factories;

use Attack\Controller\Logic\Factories\Interfaces\LogicFactoryInterface;
use Attack\Controller\Logic\Operations\LogicLandMove;

class LandMoveLogicFactory implements LogicFactoryInterface {

	public function getIdPhase() {
		return PHASE_LANDMOVE;
	}

	public function getOperation($id_game) {
		return new LogicLandMove($id_game);
	}

}
