<?php
class LandMoveFactory implements ContentFactoryInterface {
	
	public function getName() {
		return 'landmove';
	}
	
	public function getOperation($id_user,$id_game) {
		$return = new ContentLandMove($id_user,$id_game,'landmove',CHECK_SESSION_GAME_RUNNING);
		return $return;
	}
}
?>