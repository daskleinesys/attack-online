<?php
namespace Attack\Model\User;

use Attack\Exceptions\DatabaseException;
use Attack\Exceptions\LoginException;
use Attack\Exceptions\NullPointerException;
use Attack\Exceptions\UserCreationException;
use Attack\Database\SQLConnector;
use Attack\Tools\Iterator\ModelIterator;

class ModelUser implements \JsonSerializable {

    // currently (logged in) user model
    private static $current_user = null;

    // list of all user models
    private static $users = [];

    // pre-filled member_vars
    private $id = 0; // int
    private $given_name = null; // string
    private $last_name = null; // string
    private $login = null; // string
    private $email = null; // string
    private $status = null; // enum ('inactive','active','moderator','admin','deleted')
    private $verify = null; // string
    private $token = null; // string

    /**
     * creates a new user-object, that loads user-data
     *
     * @param int $id_user (object gets filled when id given)
     * @throws NullPointerException
     * @return ModelUser
     */
    private function __construct($id_user = 0) {
        $this->id = intval($id_user);

        // dummy user
        if ($this->id === 0) {
            $this->fill_default_vars();
            return $this;
        }

        // fill user data
        if (!$this->fill_member_vars()) {
            throw new NullPointerException('User not found.');
        }
        return $this;
    }

    /**
     * returns user model for given id
     *
     * @param int $id_user
     * @throws NullPointerException (if user not found)
     * @return ModelUser
     */
    public static function getUser($id_user) {
        if (isset(self::$users[$id_user])) {
            return self::$users[$id_user];
        }

        return self::$users[$id_user] = new ModelUser($id_user);
    }

    /**
     * returns an iterator for user
     *
     * @param string $status - define for user status
     * @param int $id_game
     * @return ModelIterator
     * @throws NullPointerException
     * @throws DatabaseException
     */
    public static function iterator($status = null, $id_game = null) {
        $users = array();
        $query = 'get_all_users';
        $dict = array();

        // select status
        if ($status !== STATUS_USER_ALL && $status != null && $id_game != null) {
            $query = 'get_user_by_status_game';
            $dict[':status'] = $status;
            $dict[':id_game'] = intval($id_game);
        } else if ($status !== STATUS_USER_ALL && $status != null) {
            $query = 'get_user_by_status';
            $dict[':status'] = $status;
        } else if ($id_game != null) {
            $query = 'get_user_by_game';
            $dict[':id_game'] = intval($id_game);
        }

        // query user
        try {
            $result = SQLConnector::Singleton()->epp($query, $dict);
        } catch (DatabaseException $ex) {
            throw $ex;
        }

        foreach ($result as $user) {
            $id_user = $user['id'];
            if (!isset(self::$users[$id_user])) {
                self::$users[$id_user] = new ModelUser($id_user);
            }
            $users[] = self::$users[$id_user];
        }

        return new ModelIterator($users);
    }

    /**
     * tries to create a new user, returns false if login is already in use
     *
     * @param string $name
     * @param string $lastname
     * @param string $login
     * @param string $password
     * @param string $email
     * @param string $verify (random string used for account activation)
     * @throws DatabaseException
     * @throws UserCreationException
     * @return ModelUser
     */
    public static function create($name, $lastname, $login, $password, $email, $verify) {
        // check if login-name is taken
        $result = SQLConnector::Singleton()->epp('check_if_login_exists', array(':login' => $login));
        if (!empty($result)) {
            throw new UserCreationException('Username already taken.');
        }
        // check if email is taken
        $result = SQLConnector::Singleton()->epp('check_if_email_exists', array(':email' => $email));
        if (!empty($result)) {
            throw new UserCreationException('E-Mail already taken.');
        }

        $data = array();
        $data[':name'] = $name;
        $data[':lastname'] = $lastname;
        $data[':login'] = $login;
        $data[':password'] = $password;
        $data[':email'] = $email;
        $data[':verify'] = $verify;
        SQLConnector::Singleton()->epp('insert_user', $data);

        // create user
        $id_newuser = SQLConnector::Singleton()->getLastInsertId();

        // user default setting for game infos
        ModelInGamePhaseInfo::getInGamePhaseInfo($id_newuser);

        return ModelUser::getUser($id_newuser);
    }

    /**
     * tries to log a user in, if successfull loads userdata
     *
     * @param string $user_name
     * @param string $password
     * @throws LoginException
     * @return ModelUser
     */
    public static function login($user_name, $password) {
        $result = SQLConnector::Singleton()->epp('check_user_login', array(':username' => $user_name, ':password' => $password));

        if (empty($result)) {
            throw new LoginException('Username/password wrong.');
        }

        self::$current_user = ModelUser::getUser($result[0]['id']);

        return self::$current_user;
    }

    /**
     * tries to log a user in, if successfull loads userdata
     *
     * @param string $token
     * @throws LoginException
     * @return ModelUser
     */
    public static function loginWithToken($token) {
        $result = SQLConnector::Singleton()->epp('check_user_token', array(':token' => $token));

        if (empty($result)) {
            throw new LoginException('Invalid token.');
        }

        self::$current_user = ModelUser::getUser($result[0]['id']);

        return self::$current_user;
    }

    /**
     * sets the current user to model with given id (or resets to dummy model if id === 0)
     *
     * @param $id_user int
     * @return ModelUser
     */
    public static function setCurrentUser($id_user = 0) {
        $id = intval($id_user);

        // set user model
        if ($id === 0) {
            self::$current_user = null;
        } else {
            self::$current_user = self::getUser($id);
        }

        return self::getCurrentUser();
    }

    /**
     * @param $username
     * @return ModelUser|null
     * @throws DatabaseException
     * @throws NullPointerException
     */
    public static function setCurrentUserByName($username) {
        $result = SQLConnector::Singleton()->epp('get_user_by_name', [':username' => $username]);

        if (empty($result)) {
            throw new NullPointerException('unknown user');
        }

        self::$current_user = ModelUser::getUser($result[0]['id']);

        return self::$current_user;
    }

    /**
     * returns the currently logged-in user, or a dummy-object if no user is logged in
     *
     * @return ModelUser
     */
    public static function getCurrentUser() {
        if (self::$current_user === null) {
            self::$current_user = new ModelUser();
        }

        return self::$current_user;
    }

    /**
     * @brief returns all view-relevant user-data as associative array
     *
     * @return array
     */
    public function getViewData() {
        $data = array(
            'given_name' => $this->given_name,
            'last_name' => $this->last_name,
            'login' => $this->login,
            'email' => $this->email,
            'status' => $this->status
        );

        $data['loggedIn'] = ($this->id > 0);

        return $data;
    }

    /**
     * checks if the given password is correct
     *
     * @param string $password
     * @return bool true if password is correct
     */
    public function checkPassword($password) {
        $result = SQLConnector::Singleton()->epp('check_user_password', array(':id_user' => $this->id, ':password' => $password));
        if (empty($result)) {
            return false;
        }
        return true;
    }

    /**
     * checks if the user is in the given game
     *
     * @param int $id_game
     * @return bool
     */
    public function checkIfUserIsInThisGame($id_game) {
        $iter = ModelUser::iterator($id_game);
        while ($iter->hasNext()) {
            if ($iter->next() == $this) return true;
        }
        return false;
    }

    /**
     * activates a inactive or deleted user account
     *
     * @return bool
     */
    public function setUserToActive() {
        SQLConnector::Singleton()->epp('set_user_status', array(':status' => STATUS_USER_ACTIVE, ':id_user' => $this->id));
        $this->status = STATUS_USER_ACTIVE;
        return true;

    }

    /**
     * sets a user account to inactive
     *
     * @return bool
     */
    public function deactivateUser() {
        SQLConnector::Singleton()->epp('set_user_status', array(':status' => STATUS_USER_INACTIVE, ':id_user' => $this->id));
        $this->status = STATUS_USER_INACTIVE;
        return true;
    }

    /**
     * sets a user to moderator
     *
     * @return bool
     */
    public function setUserToModerator() {
        SQLConnector::Singleton()->epp('set_user_status', array(':status' => STATUS_USER_MODERATOR, ':id_user' => $this->id));
        $this->status = STATUS_USER_MODERATOR;
        return true;
    }

    /**
     * sets a user to admin
     *
     * @return bool
     */
    public function setUserToAdmin() {
        SQLConnector::Singleton()->epp('set_user_status', array(':status' => STATUS_USER_ADMIN, ':id_user' => $this->id));
        $this->status = STATUS_USER_ADMIN;
        return true;
    }

    /**
     * sets a user to deleted
     *
     * @return bool
     */
    public function setUserToDeleted() {
        SQLConnector::Singleton()->epp('set_user_status', array(':status' => STATUS_USER_DELETED, ':id_user' => $this->id));
        $this->status = STATUS_USER_DELETED;
        return true;
    }

    /**
     * @param string $email
     */
    public function setNewEmail($email) {
        $this->email = $email;
        SQLConnector::Singleton()->epp('set_user_email', array(':id_user' => $this->id, ':email' => $email));
    }

    /**
     * @param string $password
     */
    public function setNewPassword($password) {
        SQLConnector::Singleton()->epp('set_user_password', array(':id_user' => $this->id, ':password' => $password));
    }

    /**
     * @param string $token
     */
    public function setToken($token) {
        SQLConnector::Singleton()->epp('set_user_token', array(':id_user' => $this->id, ':token' => $token));
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getUserId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->given_name;
    }

    /**
     * @return string
     */
    public function getLastName() {
        return $this->last_name;
    }

    /**
     * @return string
     */
    public function getEMail() {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getLogin() {
        return $this->login;
    }

    /**
     * @return string
     */
    public function getVerify() {
        return $this->verify;
    }

    /**
     * @return string
     */
    public function getToken() {
        return $this->token;
    }

    private function fill_member_vars() {
        $result = SQLConnector::Singleton()->epp('get_user_by_id', array(':id_user' => $this->id));
        if (empty($result)) {
            return false;
        }
        $data = $result[0];
        $this->given_name = $data['name'];
        $this->last_name = $data['lastname'];
        $this->login = $data['login'];
        $this->email = $data['email'];
        $this->status = $data['status'];
        $this->verify = $data['verify'];
        $this->token = $data['token'];

        return true;
    }

    private function fill_default_vars() {
        $this->id = 0;
        $this->given_name = null;
        $this->last_name = null;
        $this->login = null;
        $this->email = null;
        $this->status = null;
        $this->verify = null;
        $this->token = null;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'login' => $this->login,
        ];
    }
}
