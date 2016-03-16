<?php
namespace AttOn\Controller\Logic\Factories;

use AttOn\Controller\Logic\Factories\Interfaces\LogicFactoryInterface;
use AttOn\Controller\Logic\Operations\LogicSelectStart;

class SelectStartLogicFactory implements LogicFactoryInterface {

	public function getIdPhase() {
		return PHASE_SELECTSTART;
	}

	public function getOperation($id_game) {
		return new LogicSelectStart($id_game);
	}

}