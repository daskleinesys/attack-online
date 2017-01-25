<?php
namespace Attack\View\Content\Factories;

use Attack\View\Content\Operations;

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
