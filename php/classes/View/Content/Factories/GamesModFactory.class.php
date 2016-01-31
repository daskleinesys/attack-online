<?php
namespace AttOn\View\Content\Factories;

class GamesModFactory extends Interfaces\ContentFactory {

	public function getName() {
		return 'gamesmod';
	}

	public function getOperation() {
        $this->checkAuth(CHECK_SESSION_MOD);
		$return = new ContentGamesMod();
		return $return;
	}

}
