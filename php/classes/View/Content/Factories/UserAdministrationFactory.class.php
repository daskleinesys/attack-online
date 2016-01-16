<?php
class UserAdministrationFactory implements ContentFactoryInterface {
	
	public function getName() {
		return 'user';
	}
	
	public function getOperation($id_user,$id_game) {
		$return = new ContentUserAdministration($id_user,$id_game,'user',CHECK_SESSION_ADMIN);
		return $return;
	}
}
?>