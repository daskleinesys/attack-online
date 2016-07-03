<?php
namespace AttOn\View\Content\Operations;

use AttOn\Controller\Game\InGame\SetShipsController;
use AttOn\Exceptions\ControllerException;
use AttOn\Model\Game\ModelGame;
use AttOn\Model\User\ModelUser;

class ContentSetShips extends Interfaces\ContentOperation {

    /* @var SetShipsController */
    private $moveController;

    public function getTemplate() {
        return 'setships';
    }

    public function run(array &$data) {
        $data['template'] = $this->getTemplate();
        $this->addCurrentGameInfo($data);

        $this->moveController = new SetShipsController(ModelUser::getCurrentUser()->getId(), ModelGame::getCurrentGame()->getId());

        // update moves
        if (isset($_POST['setship'])) {
            $this->setShip($data);
        }
        if (isset($_POST['fixate_start'])) {
            $this->fixateMove($data);
        }

        // show already set ships
        $data['currentShips'] = array('gotcha');

        // show still available ships
        $data['availableShips'] = array('test');

        $this->checkFixate($data, PHASE_SETSHIPS);
        $this->checkCurrentPhase($data, PHASE_SETSHIPS);
    }

    private function setShip(array &$data) {
        // get post data and create new move via controller
        // TODO : insert new move via SetShipsController
    }

    private function fixateMove(array &$data) {
        try {
            $this->moveController->finishMove();
        } catch (ControllerException $ex) {
            $data['errors'] = array(
                'message' => $ex->getMessage()
            );
        }
    }

}
