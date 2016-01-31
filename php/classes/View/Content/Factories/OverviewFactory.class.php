<?php
namespace AttOn\View\Content\Factories;

class OverviewFactory extends Interfaces\ContentFactory {

	public function getName() {
		return 'overview';
	}

	public function getOperation() {
        $this->checkAuth(CHECK_SESSION_GAME);
		$return = new ContentOverview();
		return $return;
	}

}
