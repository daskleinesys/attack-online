<?php
namespace AttOn\View\Content\Operations;

use AttOn\View\Content\Operations\Interfaces\ContentOperation;

class ContentLandMove extends ContentOperation {

    public function getTemplate() {
        return 'landmove';
    }

    public function run(array &$data) {
        $data['template'] = $this->getTemplate();
        $this->addCurrentGameInfo($data);
        return;

        $this->parseGame($data);
        $this->handleInput();
        $this->showMoves();
        if (!$this->checkFixate()) {
            $this->showNewMove();
        }
    }

    private function showMoves() {
        // show unit description
        $unit_iter = ModelLandUnit::iterator();
        while ($unit_iter->hasNext()) {
            $_Unit = $unit_iter->next();
            $this->xtpl->assign('abbr', $_Unit->getAbbreviation());
            $this->xtpl->assign('unit', $_Unit->getName());
            $this->xtpl->parse('main.unit');
            $this->xtpl->parse('main.unitdescription');
            $this->xtpl->parse('main.newmove.unit');
        }

        // show moves
        $_Game = ModelGame::getGame($this->id_game_logged_in);
        $id_game = $_Game->getId();
        $round = $_Game->getRound();
        $phase = $_Game->getIdPhase();
        if ($phase > PHASE_LANDMOVE) $round++;

        $move_iter = ModelLandMove::iterator($id_game, $round, $this->id_user_logged_in);
        while ($move_iter->hasNext()) {
            $_Move = $move_iter->next();
            $move = array();
            $move['id'] = $_Move->getIdMove();

            $steps = $_Move->getSteps();
            $_ZArea = ModelGameArea::getGameArea($id_game, $steps[1]);
            $_Area = ModelArea::getArea($_ZArea->getIdArea());
            $move['start'] = $_Area->getNumber() . ' ' . $_Area->getName();

            $steps = $_Move->getSteps();
            $_ZArea = ModelGameArea::getGameArea($id_game, $steps[count($steps)]);
            $_Area = ModelArea::getArea($_ZArea->getIdArea());
            $move['destination'] = $_Area->getNumber() . ' ' . $_Area->getName();

            $this->xtpl->assign('move', $move);

            $units = $_Move->getUnits();
            $unit_iter = ModelLandUnit::iterator();
            while ($unit_iter->hasNext()) {
                $_Unit = $unit_iter->next();
                $id_unit = $_Unit->getId();
                $count = (isset($units[$id_unit])) ? $units[$id_unit] : 0;
                $this->xtpl->assign('count', $count);
                $this->xtpl->parse('main.move.unit');
            }

            if (!$this->checkFixate()) {
                $this->xtpl->parse('main.move.delete');
            }

            $this->xtpl->parse('main.move');
        }
    }

    private function showNewMove() {
        $iter = ModelArea::iterator(TYPE_LAND);
        while ($iter->hasNext()) {
            $_Area = $iter->next();
            $_ZArea = ModelGameArea::getGameAreaForArea($this->id_game_logged_in, $_Area->getId());
            $area = array();
            $area['idzarea'] = $_ZArea->getId();
            $area['number'] = $_Area->getNumber();
            $area['name'] = $_Area->getName();
            $this->xtpl->assign('area', $area);
            if ($_ZArea->getIdUser() == $this->id_user_logged_in) $this->xtpl->parse('main.newmove.startarea');
            $this->xtpl->parse('main.newmove.destinationarea');
        }

        $_Game = ModelGame::getGame($this->id_game_logged_in);
        if ($_Game->getIdPhase() != PHASE_LANDMOVE) $this->xtpl->parse('main.notactualphase');

        $this->xtpl->parse('main.newmove');
        $this->xtpl->parse('main.fixate');
    }

    private function checkFixate() {
        $_IGPI = ModelInGamePhaseInfo::getInGamePhaseInfo($this->id_user_logged_in, $this->id_game_logged_in);
        if ($_IGPI->getIsReadyForPhase(PHASE_LANDMOVE) == 1) {
            return true;
        }
        return false;
    }

    private function handleInput() {

        if (empty($_POST)) return;
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
