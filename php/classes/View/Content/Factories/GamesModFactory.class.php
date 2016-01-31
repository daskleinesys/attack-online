<?php
namespace AttOn\View\Content\Factories;
use AttOn\View\Content\Operations;

class GamesModFactory extends Interfaces\ContentFactory {

	public function getName() {
		return 'gamesmod';
	}

	public function getOperation() {
        $this->checkAuth(CHECK_SESSION_MOD);
		$return = new Operations\ContentGamesMod();
		return $return;
	}

}
