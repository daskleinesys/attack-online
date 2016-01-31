<?php
namespace AttOn\View\Content\Factories;

class GamesFactory extends Interfaces\ContentFactory {

	public function getName() {
		return 'games';
	}

	public function getOperation() {
        $this->checkAuth(CHECK_SESSION_USER);
		$return = new ContentGames();
		return $return;
	}

}
