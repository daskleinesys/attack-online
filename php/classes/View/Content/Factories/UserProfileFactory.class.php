<?php
namespace AttOn\View\Content\Factories;

class UserProfileFactory extends Interfaces\ContentFactory {

	public function getName() {
		return 'profile';
	}

	public function getOperation() {
        $this->checkAuth(CHECK_SESSION_USER);
		$return = new ContentUserProfile();
		return $return;
	}

}
