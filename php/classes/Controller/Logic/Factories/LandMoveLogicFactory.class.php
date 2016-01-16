<?php
class LandMoveLogicFactory implements LogicFactoryInterface {
	
	public function getPhase() {
		return PHASE_LANDMOVE;
	}
	
	public function getOperation($id_game) {
		return new LogicLandMove($id_game);
	}
}
?>