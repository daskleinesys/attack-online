<?php
namespace AttOn\Tools;
use AttOn\Model\User;

class CheckSessions {

	public static function checkCookies() {
		if (isset($_SESSION['user_id']) && intval($_SESSION['user_id']) > 0) {
            User\ModelUser::setCurrentUser($_SESSION['user_id']);
            return true;
        }
        // TODO : do not set user_id into cookie
		if (!isset($_COOKIE['user_id'])) {
            return false;
        }
		$_SESSION['user_id'] = $_COOKIE['user_id'];
        User\ModelUser::setCurrentUser($_SESSION['user_id']);
		return true;
	}

}
?>