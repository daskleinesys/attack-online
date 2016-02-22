<?php
namespace AttOn\Controller\Logic\Factories;

use AttOn\Controller\Logic\Factories\Interfaces\LogicFactoryInterface;
use AttOn\Controller\Logic\Operations\LogicLandMove;

class LandMoveLogicFactory implements LogicFactoryInterface {

	public function getPhase() {
		return PHASE_LANDMOVE;
	}

	public function getOperation($id_game) {
		return new LogicLandMove($id_game);
	}

}
