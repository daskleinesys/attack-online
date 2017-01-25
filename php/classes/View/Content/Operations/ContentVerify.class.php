<?php
namespace Attack\View\Content\Operations;

use Attack\Controller\User\UserActions;
use Attack\Exceptions\ControllerException;
use Attack\View\Content\Operations\Interfaces\ContentOperation;

class ContentVerify extends ContentOperation {

    public function getTemplate() {
        return 'verify';
    }

    public function run(array &$data) {
        $data['template'] = $this->getTemplate();

        if (!isset($_GET['user_id']) || !isset($_GET['verificationCode'])) {
            $data['errors'] = array(
                'message' => 'Missing parameters'
            );
            return;
        }

        try {
            UserActions::verifyAccount(intval($_GET['user_id']), $_GET['verificationCode']);
            $data['success'] = true;
            return;
        } catch (ControllerException $ex) {
            $data['errors'] = array(
                'message' => $ex->getMessage()
            );
        } catch (\Exception $ex) {
            $data['errors'] = array(
                'message' => 'Unexpected error. Please contact an admin.'
            );
        }
    }
}
