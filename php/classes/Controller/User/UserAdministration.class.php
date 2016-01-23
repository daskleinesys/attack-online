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

	/**
	 * updates standard notification rules
	 * @throws NullPointerException
	 * @param array $rules - dict(int id_phase => bool)
	 */
	public function updateStandardIngameNotificationRules($rules) {
		foreach ($rules as $id_phase => $rule) {
			ModelInGamePhaseInfo::getInGamePhaseInfo($this->id_user,0)->setNotificationRule($id_phase,$rule);
		}
	}

	/**
	 * @throws NullPointerException, ControllerException
	 * @param array $rules - dict(int id_game => dict(int id_phase => bool))
	 */
	public function updateIngameNotificationRules($rules) {
		foreach ($rules as $id_game => $notif_rules) {
			if (!$this->checkInGame($id_game)) throw new ControllerException('User not in this game!');
			foreach ($notif_rules as $id_phase => $rule) {
				ModelInGamePhaseInfo::getInGamePhaseInfo($this->id_user,$id_game)->setNotificationRule($id_phase,$rule);
			}
		}
	}

	/**
	 *
	 * changes email and/or password if given, correct old password needed !
	 * @param string $email
	 * @param string $new_password1
	 * @param string $new_password2
	 * @param string $password
	 * @throws ControllerException
	 * @return boolean - true on success
	 */
	public function updateAccountData($email, $new_password1, $new_password2, $password) {
		if (empty($email) && empty($new_password1) && empty($new_password2)) return false;
		$new_mail = false;
		$new_password = false;

		if (!empty($email)) {
			if (!preg_match("/^([a-zA-Z0-9._%+-]{1,30}@[a-zA-Z0-9.-]{1,30}\.[a-zA-Z]{2,4})?$/",$email)) throw new ControllerException('Invalid E-Mail.');
			$new_mail = true;
		}

		if ((!empty($new_password1)) || (!empty($new_password2))) {
			if ($new_password1 !== $new_password2) throw new ControllerException('New passwords don\'t match.');
			if (!preg_match("/^([a-zA-Z0-9$%'-]{5,})?$/",$user_password1)) throw new ControllerException('Invalid new password.');
			$new_password = true;
		}

		// check password
		if (empty($password)) throw new ControllerException('Please enter your old password.');
		if (!preg_match("/^([a-zA-Z0-9$%'-]{5,})?$/",$password)) throw new ControllerException('Invalid password.');

		// check password
		$_User = Modeluser::getUser($this->id_user);
		if (!$_User->checkPassword($password)) throw new ControllerException('Wrong password!');

		if ($new_mail) $_User->setNewEmail($email);
		if ($new_password) $_User->setNewPassword($new_password1);
		return true;
	}
}
?>