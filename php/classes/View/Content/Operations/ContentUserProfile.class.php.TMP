<?php
class ContentUserProfile extends ContentOperation {

	private $_UserActions;

	public function run() {

		$this->_UserActions = new UserActions($this->id_user_logged_in);

		// change get-informed rules
		if (isset($_POST['submit_message_rules'])) {
			try {
				if ($this->updateGetsInformed()) $this->showContentInfo('Notifications successfully updated.');
			} catch (ControllerException $ex) {
				$this->showContentError($ex->getMessage());
			}
		}

		// change account data
		if (isset($_POST['change_account_data'])) {
			try {
				if ($this->updateUserData()) $this->showContentInfo('User successfully updated.');
			} catch (ControllerException $ex) {
				$this->showContentError($ex->getMessage());
			}
		}

		$this->showGetsInformed();
		$this->showUserData();

		$this->xtpl->parse('main');
		$this->xtpl->out('main');

		return true;
	}

	private function updateGetsInformed() {

		//game rules
		if (isset($_POST['games'])) {
			$rules = array();
			foreach ($_POST['games'] as $id_game) {
				if (!isset($rules[$id_game])) $rules[$id_game] = array();
				$iter = ModelPhase::iterator();
				while ($iter->hasNext()) {
					$id_phase = $iter->next()->getId();
					if (isset($_POST[$id_game . '_' . $id_phase])) {
						$rules[$id_game][$id_phase] = ($_POST[$id_game . '_' . $id_phase]) ? true : false;
					}
				}
			}
			$this->_UserActions->updateIngameNotificationRules($rules);
		}

		// check default rules
		$game_id = 0;
		$rules = array();
		$iter = ModelPhase::iterator();
		while ($iter->hasNext()) {
			$id_phase = $iter->next()->getId();
			if (isset($_POST['0_' . $id_phase])) {
				$rules[$id_phase] = ($_POST['0_' . $id_phase]) ? true : false;
			}
		}
		$this->_UserActions->updateStandardIngameNotificationRules($rules);

		return true;
	}

	private function updateUserData() {
		if (!isset($_POST['email']) || !isset($_POST['password1']) || !isset($_POST['password2']) || !isset($_POST['password'])) throw new Exception('Sorry, not all required POST-Vars submitted.');
		$result = $this->_UserActions->updateAccountData(trim($_POST['email']), trim($_POST['password1']), trim($_POST['password2']), trim($_POST['password']));
		if ($result) return true;
		else return false;
	}

	private function showGetsInformed() {
		// parse phase-overview
		$iter = ModelPhase::iterator();
		
		while ($iter->hasNext()) {
			$this->xtpl->assign('phase',$iter->next()->getName());
			$this->xtpl->parse('main.phase');
		}

		// parse game-rules
		$iter_games = ModelGame::iterator(GAME_STATUS_ALL,$this->id_user_logged_in);
		while ($iter_games->hasNext()) {
			$_Game = $iter_games->next();
			$this->xtpl->assign('game_name',$_Game->getName());
			$this->xtpl->assign('id_game',$_Game->getId());
			$_InGamePhaseInfo = ModelInGamePhaseInfo::getInGamePhaseInfo($this->id_user_logged_in,$_Game->getId());
			foreach ($_InGamePhaseInfo->getNotificationRules() as $id_phase => $rule) {
				$this->xtpl->assign('id_phase',$id_phase);
				if (!$rule) {
					$this->xtpl->assign('no_selected','selected="selected"');
					$this->xtpl->assign('yes_selected','');
				}
				else {
					$this->xtpl->assign('yes_selected','selected="selected"');
					$this->xtpl->assign('no_selected','');
				}
				$this->xtpl->parse('main.game.phase');
			}
			
			$this->xtpl->parse('main.game');
		}

		// parse standard-game-rules
		$_InGamePhaseInfo = ModelInGamePhaseInfo::getInGamePhaseInfo($this->id_user_logged_in);
		foreach ($_InGamePhaseInfo->getNotificationRules() as $id_phase => $rule) {
			$this->xtpl->assign('id_phase',$id_phase);
			if (!$rule) {
				$this->xtpl->assign('no_selected','selected="selected"');
				$this->xtpl->assign('yes_selected','');
			}
			else {
				$this->xtpl->assign('yes_selected','selected="selected"');
				$this->xtpl->assign('no_selected','');
			}
			$this->xtpl->parse('main.standard_phase');
		}
	}

	private function showUserData() {
		if (isset($_POST['email'])) if (preg_match("/^([a-zA-Z0-9._%+-]{1,30}@[a-zA-Z0-9.-]{1,30}\.[a-zA-Z]{2,4})?$/",$_POST['email'])) $this->xtpl->assign('user_email',trim($_POST['email']));
		$_User = ModelUser::getUser($this->id_user_logged_in);
		$user = array();
		$user['name'] = $_User->getName();
		$user['lastname'] = $_User->getLastName();
		$user['email'] = $_User->getEMail();
		$user['login'] = $_User->getLogin();
		$this->xtpl->assign('user',$user);
	}



}
?>