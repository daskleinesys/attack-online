<?php
namespace Attack\View\Content\Factories;

use Attack\View\Content\Operations;

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
