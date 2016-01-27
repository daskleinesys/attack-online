<?php
namespace AttOn\Tools;
use AttOn\Model\User\ModelUser;
use AttOn\Exceptions;

class CheckSessions {

	public static function checkCookies() {
		if (isset($_SESSION['user_id']) && intval($_SESSION['user_id']) > 0) {
            ModelUser::setCurrentUser($_SESSION['user_id']);
            return true;
        }
		if (!isset($_COOKIE['user_token'])) {
            return false;
        }
        try {
            $user_id = ModelUser::loginWithToken($_COOKIE['user_token']);
            $_SESSION['user_id'] = $user_id;
        } catch (Exceptions\LoginException $ex) {
            return false;
        }
		return true;
	}

}
