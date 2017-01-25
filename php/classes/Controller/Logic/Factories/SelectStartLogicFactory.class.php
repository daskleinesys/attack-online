<?php
namespace Attack\Controller\Logic\Factories;

use Attack\Controller\Logic\Factories\Interfaces\LogicFactoryInterface;
use Attack\Controller\Logic\Operations\LogicSelectStart;

class SelectStartLogicFactory implements LogicFactoryInterface {

	public function getIdPhase() {
		return PHASE_SELECTSTART;
	}

	public function getOperation($id_game) {
		return new LogicSelectStart($id_game);
	}

}