<?php
class UserAdministration extends ConstrictedController {
	private $_Logger;
	
	/**
	 * @param int $id_user - id of the user accessing the moderation actions
	 */
	public function __construct($id_user) {
		parent::__construct($id_user);
		$this->_Logger = Logger::getLogger('UserAdministraion');
	}

	/**
	 * changes the state of a user to the given one
	 * @param string $new_state (use defines)
	 * @param int $id_user
	 * @throws UserAdministrationException
	 * @return boolean
	 */
	public function changeUserState($new_state,$id_user) {
		if (!$this->checkAdmin()) throw new UserAdministrationException('User not allowed to use these action.');
		$id_user = intval($id_user);
		try {
			$_User = ModelUser::getUser($id_user);
			switch ($new_state) {
				case STATUS_USER_ACTIVE:
					$_User->setUserToActive();
					break;
				case STATUS_USER_ADMIN:
					$_User->setUserToAdmin();
					break;
				case STATUS_USER_DELETED:
					$_User->setUserToDeleted();
					break;
				case STATUS_USER_INACTIVE:
					$_User->deactivateUser();
					break;
				case STATUS_USER_MODERATOR:
					$_User->setUserToModerator();
					break;
				default:
					return false;
			}
		} catch (Exception $ex) {
			return false;
		}
		return true;
	}

	/**
	 * changes the state of a multiple users to the given one
	 * @param string $new_state (use defines)
	 * @param array(int) $user_ids
	 */
	public function changeMultipleUserState($new_state, array $user_ids) {
		if (!$this->checkAdmin()) throw new UserAdministrationException('User not allowed to use these action.');
		foreach ($user_ids as $user) {
			$this->changeUserState($new_state, $user);
		}
	}
}
?>