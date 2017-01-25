<?php
namespace Attack\View\Content\Factories;

use Attack\View\Content\Operations;

class VerifyFactory extends Interfaces\ContentFactory {

	public function getName() {
		return 'verify';
	}

	public function getOperation() {
        $this->checkAuth(CHECK_SESSION_NONE);
		$return = new Operations\ContentVerify();
		return $return;
	}

}
