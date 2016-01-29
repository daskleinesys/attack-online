<?php
namespace AttOn\View\Content\Factories;

class JoinGameFactory implements Interfaces\ContentFactoryInterface {

	public function getName() {
		return 'joingame';
	}

	public function getOperation($id_user, $id_game) {
		$return = new ContentJoinGame($id_user, $id_game, 'joingame', CHECK_SESSION_USER);
		return $return;
	}

}
