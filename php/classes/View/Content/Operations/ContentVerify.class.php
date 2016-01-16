<?php
class ContentVerify extends ContentOperation {

	public function run() {

		if (!isset($_GET['verify']) || !isset($_GET['user_id'])) {
			$this->xtpl->assign('error_msg','Please use the link given in your mail.');
			$this->xtpl->parse('main.error');
			$this->xtpl->parse('main');
			$this->xtpl->out('main');
			return;
		}

		$controller_state = UserActions::verifyAccount(intval($_GET['user_id']), $_GET['verificationCode']);

		// show affirmation if user is acitvated
		if ($controller_state == 1) {
			$this->xtpl->parse('main.affirmation');
			$this->xtpl->parse('main');
			$this->xtpl->out('main');
			return null;
		}

		switch ($controller_state) {
			case 2:
				$error_msg = 'Please use the link given in your mail.';
				break;
			case 3:
				$error_msg = 'Please use the link given in your mail.';
				break;
			case 4:
				$error_msg = 'Please use the link given in your mail.';
				break;
			case 5:
				$error_msg = 'Please use the link given in your mail.';
				break;
			default:
				$error_msg = 'Please try again later.';
				break;
		}
		
		$this->xtpl->assign('error_msg',$error_msg);
		$this->xtpl->parse('main.error');
		$this->xtpl->parse('main');
		$this->xtpl->out('main');
	}
}
?>