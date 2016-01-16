<?php
class SelectStartLogicFactory implements LogicFactoryInterface {
	
	public function getPhase() {
		return PHASE_SELECTSTART;
	}
	
	public function getOperation($id_game) {
		return new LogicSelectStart($id_game);
	}
}
?>