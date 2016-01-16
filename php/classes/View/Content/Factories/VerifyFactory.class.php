<?php
class VerifyFactory implements ContentFactoryInterface {
	
	public function getName() {
		return 'verify';
	}
	
	public function getOperation($id_user,$id_game) {
		$return = new ContentVerify($id_user,$id_game,'verify',CHECK_SESSION_NONE);
		return $return;
	}
}
?>