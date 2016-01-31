<?php
namespace AttOn\View\Content\Factories;

class SelectStartFactory extends Interfaces\ContentFactory {

	public function getName() {
		return 'selectstart';
	}

	public function getOperation() {
        $this->checkAuth(CHECK_SESSION_GAME_START);
		$return = new ContentSelectStart();
		return $return;
	}

}
