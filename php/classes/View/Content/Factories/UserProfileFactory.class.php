<?php
namespace AttOn\View\Content\Factories;
use AttOn\View\Content\Operations;

class UserProfileFactory extends Interfaces\ContentFactory {

	public function getName() {
		return 'profile';
	}

	public function getOperation() {
        $this->checkAuth(CHECK_SESSION_USER);
		$return = new Operations\ContentUserProfile();
		return $return;
	}

}
