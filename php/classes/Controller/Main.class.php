<?php
class Main {
	private $id_user = null; // logged in user
	private $id_game = null; // selected game by user

	public function execute(){
		// logger
		$logger = Logger::getLogger('Main.class.php');

		// check for set cookie
		CheckSessions::checkCookies();

		// vars to check for errors
		$session_error = false;
		$error_msg = array();
		$login_error = array();

		// create model-objects
		try {
			if (isset($_SESSION['user_id'])) {
				ModelUser::getUser($_SESSION['user_id']);
				$this->id_user = $_SESSION['user_id'];
			}
			if (isset($_SESSION['game_id'])) {
				$_UserGameInteraction = new UserGameInteraction($this->id_user);
				$_UserGameInteraction->selectGame($_SESSION['game_id']);
				$this->id_game = $_SESSION['game_id'];
			}
		} catch (ControllerException $ex) {
			$this->id_game = null;
			unset($_SESSION['game_id']);
			$error_msg[] = $ex->getMessage();
		} catch (NullPointerException $ex) {
			$logger->error($ex);
			session_unset();
			$error_msg[] = $ex->getMessage();
		} catch (Exception $ex) {
			$logger->error($ex);
			session_unset();
			$error_msg[] = 'An error occurred while loading your user. If this error continues to appear after refreshing this page please contact the site-admin.';
		}
		
		// factory pattern
		(!isset($_GET['content'])) ? $content = 'home' : $content = $_GET['content'];
		try {
			$factory_array = AttonToolKit::loadFactories('./classes/View/Content/Factories/');
		} catch (Exception $ex) {
			$factory_array = array();
			$logger->error($ex);
			$error_msg[] = 'Unable to load content. Please contact a site-admin.';
		}

		foreach ($factory_array as $factory) {
			if ($factory->getName() == $content) {
				try {
					$content_object = $factory->getOperation($this->id_user,$this->id_game);
				} catch (SessionException $ex) {
					$logger->error($ex);
					$session_error = true;
					$content_object = new ContentHome($this->id_user,$this->id_game,'home', CHECK_SESSION_NONE);
					$error_msg[] = 'Sorry, you have no permission to view this site.';
				} catch (Exception $ex) {
					$logger->error($ex);
					$error_msg[] = 'Unable to load content. Please contact a site-admin.';
				}
			}
		}

		if (!isset($content_object)) {
			$content_object = new ContentPageNotFound($this->id_user,$this->id_game,'pagenotfound',CHECK_SESSION_NONE);
		}

		// throw exception if no content is created
		if (!is_object($content_object)) {
			$logger->fatal('No Operation created!');
			throw new Exception('No Operation created!');
		}

		// login
		if (isset($_POST['login_action_required'])) {
			try {
				if (!isset($_POST['username']) || !isset($_POST['password'])) throw new LoginException('No Username/Password submitted.');
				$user_username = trim($_POST['username']);
				$user_password = trim($_POST['password']);
				if (isset($_POST['remember'])) $user_remember = true;
				else $user_remember = false;
				$this->id_user = UserActions::login($user_username,$user_password,$user_remember);
			} catch (LoginException $ex) {
				$logger->info($ex);
				$login_error[] = $ex->getMessage();
			}
		}
		
		// check if new game is selected
		if (isset($_POST['select_game'])) {
			try {
				$_UserGameInteraction = new UserGameInteraction($this->id_user);
				$this->id_game = $_UserGameInteraction->selectGame(intval($_POST['select_game']));
			} catch (Exception $ex) {
				$logger->error($ex);
				$error_msg[] = $ex->getMessage();
			}
		}

		// header
		$header = new Header($this->id_user, $this->id_game);
		$header->run();

		// Generate the navigation menu
		$navmenu = new Navmenu($this->id_user, $this->id_game);
		$navmenu->run();

		// Generate Main Frame
		$content_object->beginContent();
		foreach ($error_msg as $error) {
			$content_object->showErrorMsg($error);
		}
		foreach ($login_error as $error) {
			$content_object->showLoginError($error);
		}
		if (!$session_error) $content_object->run();
		$content_object->endContent();

		// End HP
		$footer = new Footer();
		$footer->run();

		DataSource::getInstance()->disconnect();
	}
}
?>