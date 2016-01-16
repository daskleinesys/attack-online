<?php
class OverviewFactory implements ContentFactoryInterface {
	
	public function getName() {
		return 'overview';
	}
	
	public function getOperation($id_user,$id_game) {
		$return = new ContentOverview($id_user,$id_game,'overview',CHECK_SESSION_GAME);
		return $return;
	}
}
?>