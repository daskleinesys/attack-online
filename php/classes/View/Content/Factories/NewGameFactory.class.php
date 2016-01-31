<?php
namespace AttOn\View\Content\Factories;

class NewGameFactory extends Interfaces\ContentFactory {

	public function getName() {
		return 'newgame';
	}

	public function getOperation() {
        $this->checkAuth(CHECK_SESSION_USER);
		$return = new ContentNewGame();
		return $return;
	}

}
