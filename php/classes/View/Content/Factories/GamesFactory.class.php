<?php
namespace AttOn\View\Content\Factories;
use AttOn\View\Content\Operations;

class GamesFactory extends Interfaces\ContentFactory {

	public function getName() {
		return 'games';
	}

	public function getOperation() {
        $this->checkAuth(CHECK_SESSION_USER);
		$return = new Operations\ContentGames();
		return $return;
	}

}
