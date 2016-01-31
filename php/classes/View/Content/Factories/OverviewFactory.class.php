<?php
namespace AttOn\View\Content\Factories;
use AttOn\View\Content\Operations;

class OverviewFactory extends Interfaces\ContentFactory {

	public function getName() {
		return 'overview';
	}

	public function getOperation() {
        $this->checkAuth(CHECK_SESSION_GAME);
		$return = new Operations\ContentOverview();
		return $return;
	}

}
