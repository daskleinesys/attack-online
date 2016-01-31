<?php
namespace AttOn\View\Content\Factories;

class VerifyFactory extends Interfaces\ContentFactory {

	public function getName() {
		return 'verify';
	}

	public function getOperation() {
        $this->checkAuth(CHECK_SESSION_NONE);
		$return = new ContentVerify();
		return $return;
	}

}
