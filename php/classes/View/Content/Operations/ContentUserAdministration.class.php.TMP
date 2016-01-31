<?php
class ContentUserAdministration extends ContentOperation {

	private $orderby_criteria;
	private $direction;
	private $updown;

	public function run() {

		// user overview
		if (isset($_POST['criteria'])) $this->orderby_criteria = $_POST['criteria'];
		else $this->orderby_criteria = SORT_BY_ID;
		if (isset($_POST['updown'])) {
			if ($_POST['updown'] == 'asc') $this->direction = true;
			else $this->direction = false;
			$this->updown = $_POST['updown'];
		} else {
			$this->direction = true;
			$this->updown = 'asc';
		}
		$selected = 'selected="selected"';
		$this->xtpl->assign('selected_' . $this->orderby_criteria,$selected);
		$this->xtpl->assign('selected_' . $this->updown,$selected);
		$this->xtpl->assign('criteria',$this->orderby_criteria);
		$this->xtpl->assign('updown',$this->updown);
			

		// load controller
		$_UserAdministration = new UserAdministration($this->id_user_logged_in);

		if (isset($_POST['activate_x']) && isset($_POST['action'])) $_UserAdministration->changeMultipleUserState(STATUS_USER_ACTIVE, $_POST['action']);
		if (isset($_POST['deactivate_x']) && isset($_POST['action'])) $_UserAdministration->changeMultipleUserState(STATUS_USER_INACTIVE, $_POST['action']);
		if (isset($_POST['set_moderator_x']) && isset($_POST['action'])) $_UserAdministration->changeMultipleUserState(STATUS_USER_MODERATOR, $_POST['action']);
		if (isset($_POST['set_admin_x']) && isset($_POST['action'])) $_UserAdministration->changeMultipleUserState(STATUS_USER_ADMIN, $_POST['action']);
		if (isset($_POST['affirmate_delete']) && isset($_POST['action'])) $_UserAdministration->changeMultipleUserState(STATUS_USER_DELETED, $_POST['action']);


		// affirm delete
		if (isset($_POST['delete_x']) && isset($_POST['action'])) {
			foreach ($_POST['action'] as $id_user) {
				try {
					$_User = ModelUser::getUser(intval($id_user));
				} catch (NullPointerException $ex) {
					continue;
				}
				$user = array();
				$user['id'] = $_User->getUserId();
				$user['name'] = $_User->getName();
				$user['lastname'] = $_User->getLastName();
				$user['login'] = $_User->getLogin();
				$user['email'] = $_User->getEMail();
				$user['status'] = $_User->getStatus();
				$this->xtpl->assign('user',$user);
				$this->xtpl->parse('main.delete.user');
			}
			$this->xtpl->parse('main.delete');
		}

		// checkboxes
		if (isset($_POST['checked'])) {
			$checked = 'checked="checked"';
			$this->xtpl->assign('checked',$checked);
		}
		if (isset($_POST['unchecked'])) {
			$checked = '';
			$this->xtpl->assign('checked',$checked);
		}

		// show users
		$iter = ModelUser::iterator(STATUS_USER_ALL,null,$this->orderby_criteria,$this->direction);
		while ($iter->hasNext()) {
			$_User = $iter->next();
			$user = array();
			$user['id'] = $_User->getUserId();
			$user['name'] = $_User->getName();
			$user['lastname'] = $_User->getLastName();
			$user['login'] = $_User->getLogin();
			$user['email'] = $_User->getEMail();
			$user['status'] = $_User->getStatus();


			$this->xtpl->assign('user',$user);
			if ($_User->getUserId() != $this->id_user_logged_in) {
				$this->xtpl->parse('main.user.box');
			}
			else {
				$this->xtpl->parse('main.user.nobox');
			}
			if ($_User->getStatus() != STATUS_USER_DELETED) $this->xtpl->parse('main.user');
		}

		$this->xtpl->parse('main');
		$this->xtpl->out('main');

		return true;
	}

}
?>