<?php
namespace Attack\View\Content\Operations;

use Attack\Controller\User\UserActions;
use Attack\Exceptions\ControllerException;
use Attack\Exceptions\UserCreationException;
use Attack\View\Content\Operations\Interfaces\ContentOperation;

class ContentRegister extends ContentOperation {

    public function getTemplate() {
        return 'register';
    }

    public function run(array &$data) {
        $data['template'] = $this->getTemplate();

        // if input -> use controller
        if (!isset($_POST['register'])) {
            return;
        }

        $this->register($data);
    }

    private function register(array &$data) {
        // Grab the user-entered log-in data
        $user_firstname = trim($_POST['firstname']);
        $user_lastname = trim($_POST['lastname']);
        $user_email = trim($_POST['email']);
        $user_login = trim($_POST['login']);
        $user_password1 = trim($_POST['password1']);
        $user_password2 = trim($_POST['password2']);

        try {
            UserActions::register($user_firstname, $user_lastname, $user_email, $user_login, $user_password1, $user_password2);
            $data['created'] = true;
            return;
        } catch (ControllerException $ex) {
            $data['errors'] = array(
                'message' => $ex->getMessage()
            );
        } catch (UserCreationException $ex) {
            $data['errors'] = array(
                'message' => $ex->getMessage()
            );
        } catch (\Exception $ex) {
            $data['errors'] = array(
                'message' => 'Unexpected error. Please contact an admin.'
            );
        }

        $user = array();
        if (isset($user_firstname)) {
            $user_firstname = preg_replace('%(")*(.[^"]{1,40})%', '$2', $user_firstname);
            $user['firstname'] = $user_firstname;
        }
        if (isset($user_lastname)) {
            $user_lastname = preg_replace('%(")*(.[^"]{1,40})%', '$2', $user_lastname);
            $user['lastname'] = $user_lastname;
        }
        if (isset($user_email)) {
            $user_email = preg_replace('%(")*(.[^"]{1,40})%', '$2', $user_email);
            $user['email'] = $user_email;
        }
        if (isset($user_login)) {
            $user_login = preg_replace('%(")*(.[^"]{1,40})%', '$2', $user_login);
            $user['login'] = $user_login;
        }
        $data['newuser'] = $user;
    }
}
