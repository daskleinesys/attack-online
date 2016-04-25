<?php
namespace AttOn\View\Content\Operations;

use AttOn\Exceptions\ControllerException;
use AttOn\Exceptions\NullPointerException;
use AttOn\Model\Atton\InGame\ModelGameArea;
use AttOn\Model\Atton\ModelArea;
use AttOn\Model\Atton\ModelLandUnit;
use AttOn\Model\Game\ModelGame;
use AttOn\Model\User\ModelIsInGameInfo;
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

        // TODO : create production controller

        // 1. check fixate

        // 2. delete production if necessary

        // 3. create new production (validate first)
    }

    private function showMoves(array &$data) {
        // TODO : show current moves already entered by the user
    }

    private function showCurrentProduction(array &$data) {
        $viewData = array();

        // money on bank
        $ingame = ModelIsInGameInfo::getIsInGameInfo(ModelUser::getCurrentUser()->getId(), ModelGame::getCurrentGame()->getId());
        $viewData['money'] = $ingame->getMoney();

        // money from resources
        $viewData['countries'] = 0;
        $viewData['resproduction'] = 0;
        $combos = array();
        $combos[RESOURCE_OIL] = 0;
        $combos[RESOURCE_TRANSPORT] = 0;
        $combos[RESOURCE_INDUSTRY] = 0;
        $combos[RESOURCE_MINERALS] = 0;
        $combos[RESOURCE_POPULATION] = 0;
        $iter = ModelGameArea::iterator(ModelUser::getCurrentUser()->getId(), ModelGame::getCurrentGame()->getId());
        while ($iter->hasNext()) {
            $area = $iter->next();
            ++$viewData['countries'];
            $viewData['resproduction'] += $area->getProductivity();
            ++$combos[$area->getIdResource()];
        }

        // money from traderoutes
        $viewData['traderoutes'] = 0;
        $viewData['trproduction'] = 0;

        // money from combos
        $combo_count = $combos[RESOURCE_OIL];
        foreach ($combos as $res_count) {
            if ($res_count < $combo_count) {
                $combo_count = $res_count;
            }
        }
        $viewData['combos'] = $combo_count;
        $viewData['comboproduction'] = $combo_count * 4;

        // sum
        $viewData['sum'] = $viewData['money'] + $viewData['resproduction'] + $viewData['trproduction'] + $viewData['comboproduction'];

        $data['production'] = $viewData;
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
