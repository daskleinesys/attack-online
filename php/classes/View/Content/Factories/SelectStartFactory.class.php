<?php
class SelectStartFactory implements ContentFactoryInterface {
	
	public function getName() {
		return 'selectstart';
	}
	
	public function getOperation($id_user,$id_game) {
		$return = new ContentSelectStart($id_user,$id_game,'selectstart',CHECK_SESSION_GAME_START);
		return $return;
	}
}
?>