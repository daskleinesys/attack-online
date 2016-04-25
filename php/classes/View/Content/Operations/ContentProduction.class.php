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

        $game = ModelGame::getCurrentGame();
        if ($game->getIdPhase() !== PHASE_PRODUCTION) {
            $data['notCurrentPhase'] = true;
        }

        $this->handleInput($data);
        $this->showMoves($data);
        $this->showCurrentProduction($data);

        if (!$this->checkFixate($data, PHASE_PRODUCTION)) {
            $this->showNewMove($data);
        }
    }

    private function handleInput(array &$data) {
        if (empty($_POST)) {
            return;
        }
    }

    private function showMoves(array &$data) {

    }

    private function showCurrentProduction(array &$data) {

    }

    private function showNewMove(array &$data) {
        $areasViewData = array();
        $areas = ModelArea::iterator(TYPE_LAND);
        while ($areas->hasNext()) {
            /* @var $area ModelArea */
            $area = $areas->next();
            $zArea = ModelGameArea::getGameAreaForArea(ModelGame::getCurrentGame()->getId(), $area->getId());
            $areaViewData = array();
            $areaViewData['id_zarea'] = $zArea->getId();
            $areaViewData['number'] = $area->getNumber();
            $areaViewData['name'] = $area->getName();
            if ($zArea->getIdUser() === ModelUser::getCurrentUser()->getId()) {
                $areasViewData[] = $areaViewData;
            }
        }
        $data['areas'] = $areasViewData;

        $unitsViewData = array();
        $unit_iter = ModelLandUnit::iterator();
        while ($unit_iter->hasNext()) {
            /* @var $unit ModelLandUnit */
            $unit = $unit_iter->next();
            $unitsViewData[] = array(
                'id' => $unit->getId(),
                'name' => $unit->getName()
            );
        }
        $data['units'] = $unitsViewData;
    }

}
