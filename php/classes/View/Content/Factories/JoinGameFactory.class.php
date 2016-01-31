<?php
namespace AttOn\View\Content\Factories;

class JoinGameFactory extends Interfaces\ContentFactory {

	public function getName() {
		return 'joingame';
	}

	public function getOperation() {
        $this->checkAuth(CHECK_SESSION_USER);
		$return = new ContentJoinGame();
		return $return;
	}

}
