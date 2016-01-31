<?php
namespace AttOn\View\Content\Factories;

class GameInfoFactory extends Interfaces\ContentFactory {

	public function getName() {
		return 'gameinfo';
	}

	public function getOperation() {
        $this->checkAuth(CHECK_SESSION_USER);
		$return = new ContentGameInfo();
		return $return;
	}

}
