<?php
namespace AttOn\Tools;
use AttOn\Model\User\ModelUser;
use AttOn\Exceptions;

class CheckSessions {

	public static function checkCookies() {
		if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0) {
            ModelUser::setCurrentUser($_SESSION['user_id']);
            return true;
        }
		if (!isset($_COOKIE['user_token'])) {
            return false;
        }
        try {
            $_User = ModelUser::loginWithToken($_COOKIE['user_token']);
            $_SESSION['user_id'] = $_User->getId();
        } catch (Exceptions\LoginException $ex) {
            return false;
        }
		return true;
	}

}
