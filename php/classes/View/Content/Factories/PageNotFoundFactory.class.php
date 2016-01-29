<?php
namespace AttOn\View\Content\Factories;

class PageNotFoundFactory implements Interfaces\ContentFactoryInterface {

	public function getName() {
		return 'pagenotfound';
	}

	public function getOperation($id_user, $id_game) {
		$return = new ContentHome($id_user, $id_game, 'pagenotfound', CHECK_SESSION_NONE);
		return $return;
	}

}
