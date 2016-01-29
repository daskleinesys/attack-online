<?php
namespace AttOn\View\Content\Factories;

class UserProfileFactory implements Interfaces\ContentFactoryInterface {

	public function getName() {
		return 'profile';
	}

	public function getOperation($id_user, $id_game) {
		$return = new ContentUserProfile($id_user, $id_game, 'profile', CHECK_SESSION_USER);
		return $return;
	}

}
