<?php
namespace AttOn\View\Content\Operations;

use AttOn\Controller\Game\InGame\LandMoveController;
use AttOn\Exceptions\ControllerException;
use AttOn\Exceptions\NullPointerException;
use AttOn\Model\Atton\InGame\ModelGameArea;
use AttOn\Model\Atton\InGame\Moves\ModelLandMove;
use AttOn\Model\Atton\ModelArea;
use AttOn\Model\Atton\ModelLandUnit;
use AttOn\Model\Game\ModelGame;
use AttOn\Model\User\ModelUser;
use AttOn\View\Content\Operations\Interfaces\ContentOperation;

class ContentProduction extends ContentOperation {

    public function getTemplate() {
        return 'production';
    }

    public function run(array &$data) {
        $data['template'] = $this->getTemplate();
        $this->addCurrentGameInfo($data);

        $this->handleInput($data);
        $this->showMoves($data);

        if (!$this->checkFixate($data, PHASE_PRODUCTION)) {
            $this->showNewMove($data);
        }
    }

    private function showMoves(array &$data) {
    }

    private function showNewMove(array &$data) {
    }

    private function handleInput(array &$data) {
        if (empty($_POST)) {
            return;
        }
    }

}
