<?php
namespace Attack\View\Content\Operations;

use Attack\Controller\Game\InGame\LandMoveController;
use Attack\Exceptions\ControllerException;
use Attack\Exceptions\NullPointerException;
use Attack\Model\Atton\InGame\ModelGameArea;
use Attack\Model\Atton\InGame\Moves\ModelLandMove;
use Attack\Model\Atton\ModelArea;
use Attack\Model\Atton\ModelLandUnit;
use Attack\Model\Game\ModelGame;
use Attack\Model\User\ModelUser;
use Attack\View\Content\Operations\Interfaces\ContentOperation;

class ContentLandMove extends ContentOperation {

    public function getTemplate() {
        return 'landmove';
    }

    public function run(array &$data) {
        $data['template'] = $this->getTemplate();
        $this->addCurrentGameInfo($data);

        $this->handleInput($data);
        $this->showMoves($data);

        if (!$this->checkFixate($data, PHASE_LANDMOVE)) {
            $this->showNewMove($data);
        }
        $this->checkCurrentPhase($data, PHASE_LANDMOVE);
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
    }

    private function handleInput(array &$data) {
        if (empty($_POST)) {
            return;
        }
        $controller = new LandMoveController(ModelUser::getCurrentUser()->getId(), ModelGame::getCurrentGame()->getId());

        // fixating land move
        if (isset($_POST['fixate_land_move'])) {
            $controller->finishMove();
            return;
        }

        // deleting land move
        if (isset($_POST['delete'])) {
            try {
                $controller->deleteLandMove(intval($_POST['delete']));
                $data['status'] = array(
                    'message' => 'Landzug gelöscht.'
                );
            } catch (NullPointerException $ex) {
                $data['errors'] = array(
                    'message' => $ex->getMessage()
                );
            } catch (ControllerException $ex) {
                $data['errors'] = array(
                    'message' => $ex->getMessage()
                );
            } finally {
                return;
            }
        }

        // creating new land move
        if (isset($_POST['newmove'])) {
            try {
                if (!isset($_POST['start']) || !isset($_POST['destination'])) {
                    $data['errors'] = array(
                        'message' => 'Missing parameter!'
                    );
                    return;
                }
                $units = array();
                $iter = ModelLandUnit::iterator();
                while ($iter->hasNext()) {
                    /* @var $unit ModelLandUnit */
                    $unit = $iter->next();
                    $abbr = $unit->getAbbreviation();
                    $id_unit = $unit->getId();
                    $units[$id_unit] = (isset($_POST[$abbr])) ? intval($_POST[$abbr]) : 0;
                }
                $controller->createLandMove(intval($_POST['start']), intval($_POST['destination']), $units);
                $data['status'] = array(
                    'message' => 'Landzug erstellt.'
                );
            } catch (NullPointerException $ex) {
                $data['errors'] = array(
                    'message' => $ex->getMessage()
                );
            } catch (ControllerException $ex) {
                $data['errors'] = array(
                    'message' => $ex->getMessage()
                );
            }
        }
    }

}
