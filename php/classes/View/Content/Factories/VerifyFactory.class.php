<?php
namespace AttOn\View\Content\Factories;
use AttOn\View\Content\Operations;

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
