<?php
class ContentRegister extends ContentOperation {

	private $controller_state;

	public function run() {

		// if input -> use controller
		if (isset($_POST['register'])) {
			// Grab the user-entered log-in data
			$user_name = trim($_POST['name']);
			$user_lastname = trim($_POST['lastname']);
			$user_email = trim($_POST['email']);
			$user_login = trim($_POST['login']);
			$user_password1 = trim($_POST['password1']);
			$user_password2 = trim($_POST['password2']);

			$this->controller_state = UserActions::register($user_name, $user_lastname, $user_email, $user_login, $user_password1, $user_password2);
		}

		// show site
		if (!isset($_POST['register'])) {
			$this->xtpl->parse('main.register');

			$this->xtpl->parse('main');
			$this->xtpl->out('main');
			return null;
		}

		// show affirmation if new user created
		if ($this->controller_state == 1) {
			$this->xtpl->parse('main.affirmation');
			$this->xtpl->parse('main');
			$this->xtpl->out('main');
			return null;
		}

		// show error message
		else {
			switch ($this->controller_state) {
				case 2:
					$error_msg = 'Please fill out all fields.';
					break;
				case 3:
					$error_msg = 'Invalid Name.';
					break;
				case 4:
					$error_msg = 'Invalid Family Name.';
					break;
				case 5:
					$error_msg = 'Invalid email.';
					break;
				case 6:
					$error_msg = 'Invalid Username.';
					break;
				case 7:
					$error_msg = 'Please enter the same password twice.';
					break;
				case 8:
					$error_msg = 'Password invalid.';
					break;
				case 9:
					$error_msg = 'Username allready taken.';
					break;
				default:
					$error_msg = 'Please try again later.';
					break;
			}
			$this->xtpl->assign('error_msg',$error_msg);
			$this->xtpl->parse('main.error');
		}

		if (isset($user_name)) {
			$user_name = preg_replace('%(")*(.[^"]{1,40})%','$2',$user_name);
			$this->xtpl->assign('user_name',$user_name);
		}
		if (isset($user_lastname)) {
			$user_lastname = preg_replace('%(")*(.[^"]{1,40})%','$2',$user_lastname);
			$this->xtpl->assign('user_lastname',$user_lastname);
		}
		if (isset($user_email)) {
			$user_email = preg_replace('%(")*(.[^"]{1,40})%','$2',$user_email);
			$this->xtpl->assign('user_email',$user_email);
		}
		if (isset($user_login)) {
			$user_login = preg_replace('%(")*(.[^"]{1,40})%','$2',$user_login);
			$this->xtpl->assign('user_login',$user_login);
		}
		
		$this->xtpl->parse('main.register');
		$this->xtpl->parse('main');
		$this->xtpl->out('main');
		return null;
	}
}
?>