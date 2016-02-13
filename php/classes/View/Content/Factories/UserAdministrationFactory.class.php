<?php
namespace AttOn\View\Content\Factories;
use AttOn\View\Content\Operations;

class UserAdministrationFactory extends Interfaces\ContentFactory {

	public function getName() {
		return 'user';
	}

	public function getOperation() {
        $this->checkAuth(CHECK_SESSION_ADMIN);
		$return = new Operations\ContentUserAdministration();
		return $return;
	}

}
