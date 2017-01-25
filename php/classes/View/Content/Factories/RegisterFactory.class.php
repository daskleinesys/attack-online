<?php
namespace Attack\View\Content\Factories;

use Attack\View\Content\Operations;

class RegisterFactory extends Interfaces\ContentFactory {

	public function getName() {
		return 'register';
	}

	public function getOperation() {
        $this->checkAuth(CHECK_SESSION_NONE);
		$return = new Operations\ContentRegister();
		return $return;
	}

}
