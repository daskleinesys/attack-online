<?php
namespace AttOn\View\Content\Operations;

use AttOn\Model\Atton\InGame\ModelGameArea;
use AttOn\Model\Atton\InGame\Moves\ModelLandMove;
use AttOn\Model\Atton\ModelArea;
use AttOn\Model\Atton\ModelLandUnit;
use AttOn\Model\Game\ModelGame;
use AttOn\Model\User\ModelUser;
use AttOn\View\Content\Operations\Interfaces\ContentOperation;

class ContentLandMove extends ContentOperation {

    public function getTemplate() {
        return 'landmove';
    }

    public function run(array &$data) {
        $data['template'] = $this->getTemplate();
        $this->addCurrentGameInfo($data);

        $this->handleInput($data);
        $this->showMoves($data);

        return;
        if (!$this->checkFixate($data)) {
            $this->showNewMove($data);
        }
    }

    private function showMoves(array &$data) {
        // add unit description
        $units = ModelLandUnit::iterator();
        $unitsViewData = array();
        while ($units->hasNext()) {
            /* @var $unit ModelLandUnit */
            $unit = $units->next();
            $unitsViewData[] = array(
                'id' => $unit->getId(),
                'abbreviation' => $unit->getAbbreviation(),
                'name' => $unit->getName()
            );
        }
        $data['units'] = $unitsViewData;

        // show moves
        $game = ModelGame::getCurrentGame();
        $id_game = $game->getId();
        $round = $game->getRound();
        $phase = $game->getIdPhase();
        if ($phase > PHASE_LANDMOVE) {
            ++$round;
        }

        $moves = ModelLandMove::iterator(ModelUser::getCurrentUser()->getId(), $id_game, $round);
        $movesViewData = array();
        while ($moves->hasNext()) {
            /* @var $move ModelLandMove */
            $move = $moves->next();
            $moveViewData = array();
            $moveViewData['id'] = $move->getIdMove();

            $steps = $move->getSteps();
            $zArea = ModelGameArea::getGameArea((int)$id_game, (int)array_shift($steps));
            $area = ModelArea::getArea((int)$zArea->getIdArea());
            $moveViewData['startArea'] = array(
                'number' => $area->getNumber(),
                'name' => $area->getName()
            );
            $zArea = ModelGameArea::getGameArea((int)$id_game, (int)array_pop($steps));
            $area = ModelArea::getArea($zArea->getIdArea());
            $moveViewData['destinationArea'] = array(
                'number' => $area->getNumber(),
                'name' => $area->getName()
            );

            $units = $move->getUnits();
            $unit_iter = ModelLandUnit::iterator();
            $unitsViewData = array();
            while ($unit_iter->hasNext()) {
                /* @var $unit ModelLandUnit */
                $unit = $unit_iter->next();
                $id_unit = (int)$unit->getId();
                $unitsViewData[] = array(
                    'id' => $id_unit,
                    'count' => (isset($units[$id_unit])) ? $units[$id_unit] : 0
                );
            }
            $moveViewData['units'] = $unitsViewData;
            $movesViewData[] = $moveViewData;
        }
        $data['moves'] = $movesViewData;
    }

    private function showNewMove(array &$data) {
        $startAreas = array();
        $destinationAreas = array();
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
                $startAreas[] = $areaViewData;
            }
            $destinationAreas[] = $areaViewData;
        }
        $data['startAreas'] = $startAreas;
        $data['destinationAreas'] = $destinationAreas;

        $game = ModelGame::getCurrentGame();
        if ($game->getIdPhase() !== PHASE_LANDMOVE) {
            $data['notCurrentPhase'] = true;
        }
    }

    private function checkFixate(array &$data) {
        $_IGPI = ModelInGamePhaseInfo::getInGamePhaseInfo($this->id_user_logged_in, $this->id_game_logged_in);
        if ($_IGPI->getIsReadyForPhase(PHASE_LANDMOVE) == 1) {
            return true;
        }
        return false;
    }

    private function handleInput(array &$data) {
        if (empty($_POST)) {
            return;
        }
        $_MoveController = new LandMoveController($this->id_user_logged_in, $this->id_game_logged_in);

        // deleting land move
        try {
            if (isset($_POST['delete'])) {
                $_MoveController->deleteLandMove(intval($_POST['delete']));
                $this->showContentInfo('move deleted');
            }
        } catch (NullPointerException $ex) {
            $this->showErrorMsg($ex->getMessage());
        } catch (ControllerException $ex) {
            $this->_Logger->error($ex);
            $this->showErrorMsg($ex->getMessage());
        }

        // creating new land move
        try {
            if (isset($_POST['newmove'])) {
                if (!isset($_POST['start']) || !isset($_POST['destination'])) {
                    $this->showErrorMsg('Missing parameter!');
                    return;
                }
                $units = array();
                $iter = ModelLandUnit::iterator();
                while ($iter->hasNext()) {
                    $_Unit = $iter->next();
                    $abbr = $_Unit->getAbbreviation();
                    $id_unit = $_Unit->getId();
                    $units[$id_unit] = (isset($_POST[$abbr])) ? intval($_POST[$abbr]) : 0;
                }
                $_MoveController->createLandMove(intval($_POST['start']), intval($_POST['destination']), $units);
                $this->showContentInfo('move created');
            }
        } catch (NullPointerException $ex) {
            $this->_Logger->error($ex);
            $this->showErrorMsg($ex->getMessage());
        } catch (ControllerException $ex) {
            $this->_Logger->error($ex);
            $this->showErrorMsg($ex->getMessage());
        }

        // fixating land move
        if (isset($_POST['fixate_land_move'])) $_MoveController->finishMove();

    }

}
