<?php
namespace AttOn\View\Content\Factories;

class RegisterFactory extends Interfaces\ContentFactory {

	public function getName() {
		return 'register';
	}

	public function getOperation() {
        $this->checkAuth(CHECK_SESSION_NONE);
		$return = new ContentRegister();
		return $return;
	}

}
