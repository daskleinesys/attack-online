<?php
namespace AttOn\View\Content\Factories;

class UserAdministrationFactory extends Interfaces\ContentFactory {

	public function getName() {
		return 'user';
	}

	public function getOperation() {
        $this->checkAuth(CHECK_SESSION_ADMIN);
		$return = new ContentUserAdministration();
		return $return;
	}

}
