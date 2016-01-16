<?php
class RegisterFactory implements ContentFactoryInterface {
	
	public function getName() {
		return 'register';
	}
	
	public function getOperation($id_user,$id_game) {
		$return = new ContentRegister($id_user,$id_game,'register',CHECK_SESSION_NONE);
		return $return;
	}
}
?>