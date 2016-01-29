<?php
namespace AttOn\View\Content\Factories;

class GamesModFactory implements Interfaces\ContentFactoryInterface {

	public function getName() {
		return 'gamesmod';
	}

	public function getOperation($id_user, $id_game) {
		$return = new ContentGamesMod($id_user, $id_game, 'gamesmod', CHECK_SESSION_MOD);
		return $return;
	}

}
