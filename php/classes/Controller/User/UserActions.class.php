<?php
namespace AttOn\Controller\User;
use AttOn\Model\User\ModelUser;
use AttOn\Exceptions;

class UserActions {

	/**
	 * tries to create a new user, returns a state if error or successfull
     *
	 * @param string $given_name
	 * @param string $family_name
	 * @param string $email
	 * @param string $login
	 * @param string $password1
	 * @param string $password2
	 * @return int state
	 * 1: user successfully created
	 * 2: at least one empty entry
	 * 3: given name preg_mismatch
	 * 4: last name preg_mismatch
	 * 5: email preg_mismatch
	 * 6: login preg_mismatch
	 * 7: passwords don't match
	 * 8: password preg_mismatch
	 * 9: username allready in use
	 */
	public static function register($given_name, $family_name, $email, $login, $password1, $password2) {

		// check the infos for validity
		if (empty($given_name) || empty($family_name) || empty($email) || empty($login) || empty($password1) || empty($password2)) {
			return 2;
		}
		// check given name
		if (!preg_match("/^([a-zA-Z]+[a-zA-Z' -]+[a-zA-Z']+)?$/", $given_name)) {
			return 3;
		}
		// check family name
		if (!preg_match("/^([a-zA-Z]+[a-zA-Z' -]+[a-zA-Z']+)?$/", $family_name)) {
			return 4;
		}
		// check email
		if (!preg_match("/^([a-zA-Z0-9._%+-]{1,30}@[a-zA-Z0-9.-]{1,30}\.[a-zA-Z]{2,4})?$/", $email)) {
			return 5;
		}
		// check login
		if (!preg_match("/^([a-zA-Z]+[a-zA-Z0-9]{3,})?$/", $login)) {
			return 6;
		}
		// check if passwords match
		if ($password1 !== $password2) {
			return 7;
		}
		// check password
		if (!preg_match("/^([a-zA-Z0-9$%'-]{5,})?$/", $password1)) {
			return 8;
		}

		// create new user
		$verify = range(1, 10);
		shuffle($verify);
		$verify = $verify[0] . $verify[1] . $verify[2] . $verify[3] . $verify[4] . $verify[5] . $verify[6] . $verify[7] . $verify[8] . $verify[9];

		try {
			$_User = ModelUser::create($given_name, $family_name, $login, $password1, $email, $verify);
		} catch (Exceptions\UserCreationException $ex) {
			return 9;
		}

		// mail new user info to admin
		$to = ADMIN_MAIL;
		$subject = 'New Att On Account';
		$from = 'noreply@atton.at';
		$text = 'New User:';
		$msg = "$text \n" . " " . $given_name . " " . $family_name . " " . $login;

		$headers = 'From:' . $from . "\n";

		mail($to, $subject, $msg, $headers);

		// mail verification code to the user
		$verificationCode = $_User->getVerify();
		$id_newuser = $_User->getUserId();
		$to = $this->email;
		$subject = 'Activation-Link Attack Online Account';
		$from = 'noreply@atton.at';

		$msg = '<html>
                <head>
                    <title>Activation-Link Attack Online Account</title>
                </head>
                <body>
                    <p>You just registered a new account at atton.noplace.at. To active the account please use the following link:<br />
                    <a href="http://atton.noplace.at/index.php?content=verify&verify=true&user_id=' . $id_newuser . '&verificationCode=' . $verificationCode . '">http://atton.noplace.at/index.php?content=verify&verify=true&user_id=' . $id_newuser . '&verificationCode=' . $verificationCode . '</a><br />
                    If you haven\'t created an account, please just ignore this mail.<br />
                    Your AttOn Team</p>
                </body>
            </html>
        ';

		$headers = "Content-type: text/html\n";

		mail($to, $subject, $msg, $headers);

		// return state
		return 1;
	}

	/**
	 * logs the user in (setting $_SESSION['id_user']), throws exception if anything happens
     *
	 * @param string username
	 * @param string password
	 * @param bool remember
	 * @throws LoginException
	 * @return bool, true if successfull
	 */
	public static function login($user_username, $user_password, $remember) {

		// check username
		if (!preg_match("/^([a-zA-Z]+[a-zA-Z0-9]{3,})?$/", $user_username)) {
            throw new Exceptions\LoginException('Username not valid.');
        }

		// check password
		if (!preg_match("/^([a-zA-Z0-9$%'-]{5,})?$/", $user_password)) {
            throw new Exceptions\LoginException('Password not valid.');
        }

		if (empty($user_username) || empty($user_password)) {
            throw new Exceptions\LoginException('Enter username and password.');
        }

		// try to log in
		try {
			$_User = ModelUser::login($user_username, $user_password);
		} catch (Exceptions\LoginException $ex) {
			throw $ex;
		}

		// The log-in is OK so set the user ID and username cookies, and redirect to the home page
		$_SESSION['id_user'] = $_User->getUserId();

		// set cookie
		if ($remember) {
            $auth_token = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, USER_TOKEN_MAX_LENGTH);
            $_User->setToken($auth_token);
			setcookie('user_token', $auth_token, time() + (60 * 60 * 24 * 30), ABS_REF_PREFIX);
		}
		return true;
	}

    /**
     * logout user by deleting session vars, cookies and UserModerl::$current_user
     *
     * @return void
     */
    public static function logout() {
        $_SESSION = array();
        if (isset($_COOKIE['user_token'])) {
            // Delete the user ID and username cookies by setting their expirations to an hour ago (3600)
            setcookie('user_token', '', time() - 3600, ABS_REF_PREFIX);
        }
    }

	/**
	 * tries to active a new user, returns a state if error or successfull
	 *
	 * @return int state
	 * 1: user successfully created
	 * 2: at least one empty entry
	 * 3: verification doe preg_mismatch
	 * 4: user not found
	 * 5: verification code wrong
	 *
	 */
	public static function verifyAccount($id_user, $verification_code) {

		// check input validity
		if (empty($id_user) || empty($verification_code)) {
            return 2;
        }
		$id_user = intval($id_user);
		if (!preg_match('/^([a-zA-Z0-9]+)?$/', $verification_code)) {
            return 3;
        }

		// create user model
		try {
			$_User = ModelUser::getUser($id_user);
		} catch (\Exception $ex) {
			return 4;
		}

		// check verification code
		if ($verification_code != $_User->getVerify()) {
            return 5;
        }

		// activate user (if inactive)
		if ($_User->getStatus() == STATUS_USER_INACTIVE) {
            $_User->setUserToActive();
        }

		// return success
		return 1;
	}

}
