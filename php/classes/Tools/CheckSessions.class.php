<?php
class CheckSessions {

	public static function checkCookies() {
		if (isset($_SESSION['user_id'])) return true;
		if (!isset($_COOKIE['user_id'])) return false;
		$_SESSION['user_id'] = $_COOKIE['user_id'];
		return true;
	}
	
}
?>