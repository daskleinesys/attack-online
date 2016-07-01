<?php
namespace AttOn\View\Content\Operations;

use AttOn\Controller\Game\InGame\SetShipsController;
use AttOn\Exceptions\ControllerException;
use AttOn\Model\Game\ModelGame;
use AttOn\Model\User\ModelUser;

class ContentSetShips extends Interfaces\ContentOperation {

    public function getTemplate() {
        return 'setships';
    }

    public function run(array &$data) {
        $data['template'] = $this->getTemplate();
        $this->addCurrentGameInfo($data);

        // get starting ships
        // TODO : show the user which ships are available/are already set

        // update moves
        if (isset($_POST['setships'])) {
            $this->updateSetShips($data);
        }
        if (isset($_POST['fixate_start'])) {
            $this->fixateMove($data);
        }

        $this->checkFixate($data, PHASE_SETSHIPS);
        $this->checkCurrentPhase($data, PHASE_SETSHIPS);
    }

    private function updateSetShips(array &$data) {
        // TODO : update/insert new moves via SetShipsController
    }

    private function fixateMove(array &$data) {
        $moveController = new SetShipsController(ModelUser::getCurrentUser()->getId(), ModelGame::getCurrentGame()->getId());

        try {
            $moveController->finishMove();
        } catch (ControllerException $ex) {
            $data['errors'] = array(
                'message' => $ex->getMessage()
            );
        }
    }

}
