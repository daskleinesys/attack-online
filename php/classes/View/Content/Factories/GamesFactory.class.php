<?php
class GamesFactory implements ContentFactoryInterface {
	
	public function getName() {
		return 'games';
	}
	
	public function getOperation($id_user,$id_game) {
		$return = new ContentGames($id_user,$id_game,'games',CHECK_SESSION_USER);
		return $return;
	}
}
?>