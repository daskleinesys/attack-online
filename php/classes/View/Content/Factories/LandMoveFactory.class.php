<?php
namespace AttOn\View\Content\Factories;

class LandMoveFactory extends Interfaces\ContentFactory {

	public function getName() {
		return 'landmove';
	}

	public function getOperation() {
        $this->checkAuth(CHECK_SESSION_GAME_RUNNING);
		$return = new ContentLandMove();
		return $return;
	}

}
