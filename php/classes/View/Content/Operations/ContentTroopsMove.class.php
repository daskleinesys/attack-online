<?php
namespace Attack\View\Content\Operations;

use Attack\Controller\Game\Moves\TroopsMoveController;
use Attack\Exceptions\ControllerException;
use Attack\Exceptions\NullPointerException;
use Attack\Model\Game\ModelGameArea;
use Attack\Model\Game\Moves\ModelTroopsMove;
use Attack\Model\Areas\ModelArea;
use Attack\Model\Units\ModelLandUnit;
use Attack\Model\Game\ModelGame;
use Attack\Model\User\ModelUser;
use Attack\View\Content\Operations\Interfaces\ContentOperation;

class ContentTroopsMove extends ContentOperation {

    public function getTemplate() {
        return 'troopsmove';
    }

    public function run(array &$data) {
        $data['template'] = $this->getTemplate();
        $this->addCurrentGameInfo($data);

        $this->handleInput($data);
        $this->showMoves($data);

        if (!$this->checkFixate($data, PHASE_TROOPSMOVE)) {
            $this->showNewMove($data);
        }
        $this->checkCurrentPhase($data, PHASE_TROOPSMOVE);
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
        if ($phase > PHASE_TROOPSMOVE) {
            ++$round;
        }

        $moves = ModelTroopsMove::iterator(ModelUser::getCurrentUser()->getId(), $id_game, $round);
        $movesViewData = array();
        while ($moves->hasNext()) {
            /* @var $move ModelTroopsMove */
            $move = $moves->next();
            $moveViewData = array();
            $moveViewData['id'] = $move->getIdMove();

            $steps = $move->getSteps();
            $gameArea = ModelGameArea::getGameArea((int)$id_game, (int)array_shift($steps));
            $area = ModelArea::getArea((int)$gameArea->getIdArea());
            $moveViewData['startArea'] = array(
                'number' => $area->getNumber(),
                'name' => $area->getName()
            );
            $gameArea = ModelGameArea::getGameArea((int)$id_game, (int)array_pop($steps));
            $area = ModelArea::getArea($gameArea->getIdArea());
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
            $gameArea = ModelGameArea::getGameAreaForArea(ModelGame::getCurrentGame()->getId(), $area->getId());
            $areaViewData = array();
            $areaViewData['id_game_area'] = $gameArea->getId();
            $areaViewData['number'] = $area->getNumber();
            $areaViewData['name'] = $area->getName();
            if ($gameArea->getIdUser() === ModelUser::getCurrentUser()->getId()) {
                $startAreas[] = $areaViewData;
                $destinationAreas[] = $areaViewData;
            }
        }
        $data['startAreas'] = $startAreas;
        $data['destinationAreas'] = $destinationAreas;
    }

    private function handleInput(array &$data) {
        if (empty($_POST)) {
            return;
        }
        $controller = new TroopsMoveController(ModelUser::getCurrentUser()->getId(), ModelGame::getCurrentGame()->getId());

        // fixating land move
        if (isset($_POST['fixate_move'])) {
            $controller->finishMove();
            return;
        }

        // deleting land move
        if (isset($_POST['delete'])) {
            try {
                $controller->deleteMove(intval($_POST['delete']));
                $data['status'] = array(
                    'message' => 'Move deleted.'
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

        // creating new troops move
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
                $controller->createMove(intval($_POST['start']), intval($_POST['destination']), $units);
                $data['status'] = array(
                    'message' => 'Move created.'
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
