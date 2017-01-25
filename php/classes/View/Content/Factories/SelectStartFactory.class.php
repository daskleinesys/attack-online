<?php
namespace Attack\View\Content\Factories;

use Attack\View\Content\Operations;

class SelectStartFactory extends Interfaces\ContentFactory {

	public function getName() {
		return 'selectstart';
	}

	public function getOperation() {
        $this->checkAuth(CHECK_SESSION_GAME_START);
		$return = new Operations\ContentSelectStart();
		return $return;
	}

}
