<?php
class GameInfoFactory implements ContentFactoryInterface {
	
	public function getName() {
		return 'gameinfo';
	}
	
	public function getOperation($id_user,$id_game) {
		$return = new ContentGameInfo($id_user,$id_game,'gameinfo',CHECK_SESSION_USER);
		return $return;
	}
}
?>