<?php
namespace Attack\GameLogic\Factories;

use Attack\GameLogic\Factories\Interfaces\LogicFactoryInterface;
use Attack\GameLogic\Operations\LogicSelectStart;

class SelectStartLogicFactory implements LogicFactoryInterface {

	public function getIdPhase() {
		return PHASE_SELECTSTART;
	}

	public function getOperation($id_game) {
		return new LogicSelectStart($id_game);
	}

}