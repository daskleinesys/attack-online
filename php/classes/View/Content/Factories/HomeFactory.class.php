<?php
class HomeFactory implements ContentFactoryInterface {
	
	public function getName() {
		return 'home';
	}
	
	public function getOperation($id_user,$id_game) {
		$return = new ContentHome($id_user,$id_game,'home',CHECK_SESSION_NONE);
		return $return;
	}
}
?>