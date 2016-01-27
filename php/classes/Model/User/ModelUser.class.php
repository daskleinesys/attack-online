<?php
namespace AttOn\Model\User;
use AttOn\Model\DataBase\DataSource;
use AttOn\Exceptions;

class ModelUser {

    // currently (logged in) user model
    private static $current_user = null;

	// list of all user models
	private static $users = array();

	// pre-filled member_vars
	private $id; // int
	private $given_name; // string
	private $last_name; // string
	private $login; // string
	private $email; // string
	private $status; // string
	private $verify; // string
	private $token; // string

	/**
	 * creates a new user-object, that loads user-data
     *
	 * @param integer $id_user (object gets filled when id given)
	 * @throws NullPointerException
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
            throw new Exceptions\NullPointerException('User not found.');
        }
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
	 * @param string $orderby - ('login','name','lastname','email','status')
	 * @param bool $direction - true == asc, false == desc
	 * @throws DataSourceException
	 * @return iterator
	 */
	public static function iterator($status, $id_game = null, $orderby = null, $direction = true) {
		$users = array();
		$query = 'get_users';
		$dict = array();

		// select status
		if ($status == STATUS_USER_ALL) $dict[':status'] = '%';
		else $dict[':status'] = $status;

		// select game
		if ($id_game != null) {
			$query .= '_for_game';
			$dict[':id_game'] = intval($id_game);
		}

		// order by
		if ($orderby != null) {
			$query .= '_ord_' . $orderby;
		} else $query .= '_ord_id';

		// asc/desc
		if ($direction) $query .= '_asc';
		else $query .= '_desc';

		// query user
		try {
			$result = DataSource::Singleton()->epp($query, $dict);
		} catch (Exceptions\DataSourceException $ex) {
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
	 * @throws UserCreationException
	 * @return ModelUser
	 */
	public static function create($name, $lastname, $login, $password, $email, $verify) {
		// check if login-name is taken
		$result = DataSource::Singleton()->epp('check_if_login_exists', array(':login' => $login));
		if (!empty($result)) throw new Exceptions\UserCreationException('Username already taken.');

		$data = array();
		$data[':name'] = $name;
		$data[':lastname'] = $lastname;
		$data[':login'] = $login;
		$data[':password'] = $password;
		$data[':email'] = $email;
		$data[':verify'] = $verify;
		try {
			DataSource::Singleton()->epp('create_new_user', $data);
		} catch (DataSourceException $ex) {
			throw new Exceptions\GameCreationException('Unexpected error. Please try again.');
		}

		// create user
		$result = DataSource::Singleton()->epp('get_id_for_login', array(':login' => $login));
		$id_newuser = intval($result[0]['id']);

		// user default setting for game infos
		ModelInGamePhaseInfo::getInGamePhaseInfo($id_user);

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
		$result = DataSource::Singleton()->epp('check_user_login', array(':username' => $user_name, ':password' => $password));

		if (empty($result)) {
            throw new Exceptions\LoginException('Username/password wrong.');
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
		$result = DataSource::Singleton()->epp('check_user_token', array(':token' => $token));

		if (empty($result)) {
            throw new Exceptions\LoginException('Invalid token.');
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
     * @return dictionary
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
	 * @param string $password
	 * @return bool true if password is correct
	 */
	public function checkPassword($password) {
		$result = DataSource::Singleton()->epp('check_user_password', array(':id_user' => $this->id, ':password' => $password));
		if (empty($result)) return false;
		return true;
	}

	/**
	 * checks if the user is in the given game
	 * @param int $id_game
	 * $return bool
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
	 * @return void
	 */
	public function setUserToActive() {
		DataSource::Singleton()->epp('activate_user', array(':id_user' => $this->id));
		$this->status = STATUS_USER_ACTIVE;
		return true;

	}

	/**
	 * sets a user account to inactive
	 * @return void
	 */
	public function deactivateUser() {
		DataSource::Singleton()->epp('deactivate_user', array(':id_user' => $this->id));
		$this->status = STATUS_USER_INACTIVE;
		return true;
	}

	/**
	 * sets a user to moderator
	 * @return void
	 */
	public function setUserToModerator() {
		DataSource::Singleton()->epp('set_user_to_moderator', array(':id_user' => $this->id));
		$this->status = STATUS_USER_MODERATOR;
		return true;
	}

	/**
	 * sets a user to admin
	 * @return void
	 */
	public function setUserToAdmin() {
		DataSource::Singleton()->epp('set_user_to_admin', array(':id_user' => $this->id));
		$this->status = STATUS_USER_ADMIN;
		return true;
	}

	/**
	 * sets a user to deleted
	 * @return void
	 */
	public function setUserToDeleted() {
		DataSource::Singleton()->epp('set_user_to_deleted', array(':id_user' => $this->id));
		$this->status = STATUS_USER_DELETED;
		return true;
	}

	/**
	 * @param string $email
	 */
	public function setNewEmail($email) {
		$this->email = $email;
		DataSource::Singleton()->epp('update_email', array(':id_user' => $this->id, ':email' => $email));
	}

	/**
	 * @param string $password
	 */
	public function setNewPassword($password) {
		DataSource::Singleton()->epp('update_password', array(':id_user' => $this->id, ':password' => $password));
	}

	/**
	 * @param string $password
	 */
	public function setToken($token) {
		DataSource::Singleton()->epp('update_token', array(':id_user' => $this->id, ':token' => $token));
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
		$result = DataSource::Singleton()->epp('get_all_user_data', array(':id_user' => $this->id));
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
        $this->given_name = '';
        $this->last_name = '';
        $this->login = '';
        $this->email = '';
        $this->status = '';
        $this->verify = '';
        $this->token = '';
    }
}
