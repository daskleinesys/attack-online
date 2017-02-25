<?php
namespace Attack\Controller\User;

use Attack\Exceptions\ControllerException;
use Attack\Exceptions\DatabaseException;
use Attack\Exceptions\LoginException;
use Attack\Exceptions\UserCreationException;
use Attack\Model\User\ModelUser;

class UserActions {

    /**
     * tries to create a new user, throws exception on error
     *
     * @param string $given_name
     * @param string $family_name
     * @param string $email
     * @param string $login
     * @param string $password1
     * @param string $password2
     * @return void
     * @throws ControllerException
     * @throws DatabaseException
     * @throws UserCreationException
     */
    public static function register($given_name, $family_name, $email, $login, $password1, $password2) {

        // check the infos for validity
        if (empty($given_name) || empty($family_name) || empty($email) || empty($login) || empty($password1) || empty($password2)) {
            throw new ControllerException('Parameters missing.');
        }
        // check given name
        if (!preg_match("/^([a-zA-Z]+[a-zA-Z' -]+[a-zA-Z']+)?$/", $given_name)) {
            throw new ControllerException('Invalid firstname. allowed characters: \' -a-Z');
        }
        // check family name
        if (!preg_match("/^([a-zA-Z]+[a-zA-Z' -]+[a-zA-Z']+)?$/", $family_name)) {
            throw new ControllerException('Invalid lastname. allowed characters: \' -a-Z');
        }
        // check email
        if (!preg_match("/^([a-zA-Z0-9._%+-]{1,30}@[a-zA-Z0-9.-]{1,30}\.[a-zA-Z]{2,4})?$/", $email)) {
            throw new ControllerException('Invalid email.');
        }
        // check login
        if (!preg_match("/^([a-zA-Z]+[a-zA-Z0-9]{3,})?$/", $login)) {
            throw new ControllerException('Invalid username. Only letters and numbers allowed.');
        }
        // check if passwords match
        if ($password1 !== $password2) {
            throw new ControllerException('Please enter the same password twice.');
        }
        // check password
        if (!preg_match("/^([a-zA-Z0-9$%'-]{5,})?$/", $password1)) {
            throw new ControllerException('Password invalid. Minimum of 5 characters. Allowed characters: a-Z0-9$%\'-');
        }

        // create new user
        $verify = range(1, 10);
        shuffle($verify);
        $verify = $verify[0] . $verify[1] . $verify[2] . $verify[3] . $verify[4] . $verify[5] . $verify[6] . $verify[7] . $verify[8] . $verify[9];

        $user = ModelUser::create($given_name, $family_name, $login, $password1, $email, $verify);

        // mail new user info to admin
        $to = ADMIN_MAIL;
        $subject = 'New Attack Online Account';
        $from = 'attack@thespielplatz.com';
        $text = 'New User:';
        $msg = "$text \n" . " " . $given_name . " " . $family_name . " " . $login;

        $headers = 'From:' . $from . "\n";

        mail($to, $subject, $msg, $headers);

        // mail verification code to the user
        $verificationCode = $user->getVerify();
        $id_newuser = $user->getUserId();
        $to = $email;
        $subject = 'Activation-Link Attack Online Account';
        $verificationLink = DOMAIN_ORIGIN . ABS_REF_PREFIX . 'verify/?user_id=' . $id_newuser . '&verificationCode=' . $verificationCode;

        $msg =
            '<html>
                <head>
                    <title>Activation-Link Attack Online Account</title>
                </head>
                <body>
                    <p>
                        You just registered a new account at ' . DOMAIN_ORIGIN . '. To active the account please use the following link:
                        <br />
                        <a href="' . $verificationLink . '">
                            ' . $verificationLink . '
                        </a>
                        <br />
                        If you haven\'t created an account, please just ignore this mail.
                        <br />
                        Your Attack Team
                    </p>
                </body>
            </html>';

        $headers = "Content-type: text/html\n";

        mail($to, $subject, $msg, $headers);
    }

    /**
     * logs the user in (setting $_SESSION['id_user']), throws exception if anything happens
     *
     * @param string $user_username
     * @param string $user_password
     * @param bool $remember
     * @throws LoginException
     * @return bool, true if successfull
     */
    public static function login($user_username, $user_password, $remember) {

        // check username
        if (!preg_match("/^([a-zA-Z]+[a-zA-Z0-9]{3,})?$/", $user_username)) {
            throw new LoginException('Username not valid.');
        }

        // check password
        if (!preg_match("/^([a-zA-Z0-9$%'-]{5,})?$/", $user_password)) {
            throw new LoginException('Password not valid.');
        }

        if (empty($user_username) || empty($user_password)) {
            throw new LoginException('Enter username and password.');
        }

        // try to log in
        try {
            $user = ModelUser::login($user_username, $user_password);
        } catch (LoginException $ex) {
            throw $ex;
        }

        // The log-in is OK so set the user ID and username cookies, and redirect to the home page
        $_SESSION['id_user'] = $user->getUserId();

        // set cookie
        if ($remember) {
            $auth_token = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, USER_TOKEN_MAX_LENGTH);
            $user->setToken($auth_token);
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
     * tries to active a new user
     *
     * @param int $id_user
     * @param string $verification_code
     * @throws ControllerException
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
            throw new ControllerException('Missing parameters');
        }
        $id_user = intval($id_user);
        if (!preg_match('/^([a-zA-Z0-9]+)?$/', $verification_code)) {
            throw new ControllerException('Invalid parameters');
        }

        // create user model
        try {
            $user = ModelUser::getUser($id_user);
        } catch (\Exception $ex) {
            throw new ControllerException('Invalid parameters');
        }

        // check verification code
        if ($verification_code != $user->getVerify()) {
            throw new ControllerException('Invalid parameters');
        }

        // activate user (if inactive)
        if ($user->getStatus() !== STATUS_USER_INACTIVE) {
            throw new ControllerException('Invalid parameters');
        }

        // return success
        $user->setUserToActive();
    }

}
