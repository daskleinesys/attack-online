<?php
namespace Attack\View\Content\Operations;

use Attack\Controller\Game\Moves\SetShipsController;
use Attack\Exceptions\ControllerException;
use Attack\Exceptions\ModelException;
use Attack\Model\Game\ModelGameArea;
use Attack\Model\Game\ModelGameShip;
use Attack\Model\Game\Moves\ModelSetShipsMove;
use Attack\Model\Areas\ModelArea;
use Attack\Model\Units\ModelShip;
use Attack\Model\Game\ModelGame;
use Attack\Model\User\ModelUser;

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
        if (isset($_POST['delete'])) {
            $this->deleteMove($data);
        }

        // show already set ships
        $data['currentShips'] = array();
        $iterator = ModelSetShipsMove::getSetShipMovesForUser(ModelUser::getCurrentUser()->getId(), ModelGame::getCurrentGame()->getId());
        while ($iterator->hasNext()) {
            /** @var ModelSetShipsMove $move */
            $move = $iterator->next();
            $gameShip = ModelGameShip::getShipById(ModelGame::getCurrentGame()->getId(), $move->getIdGameUnit());
            $id_game_ship = $gameShip->getIdUnit();
            $ship = ModelShip::getModelById($id_game_ship);
            $gameAreaInPort = ModelGameArea::getGameArea(ModelGame::getCurrentGame()->getId(), $move->getIdGameAreaInPort());
            $gameAreaAtSea = ModelGameArea::getGameArea(ModelGame::getCurrentGame()->getId(), $move->getIdGameArea());
            $data['currentShips'][] = array(
                'id' => $move->getId(),
                'ship_type' => $ship->getName(),
                'ship_name' => $gameShip->getName(),
                'game_area_in_port' => $gameAreaInPort->getName() . ' ' . $gameAreaInPort->getNumber(),
                'game_area_at_sea' => $gameAreaAtSea->getName() . ' ' . $gameAreaAtSea->getNumber()
            );
        }

        // show still available ships
        $data['availableShips'] = array();
        $stillAvailableShips = $this->moveController->getStillAvailableShips();
        foreach ($stillAvailableShips as $id_unit => $count) {
            if ($count <= 0) {
                continue;
            }
            $data['availableShips'][] = array(
                'id' => $id_unit,
                'count' => $count,
                'name' => ModelShip::getModelById($id_unit)->getName()
            );
        }

        // show available countries
        $data['availableGameAreasInPort'] = array();
        $data['availableGameAreasAtSea'] = array();
        $iterator = ModelGameArea::iterator(ModelUser::getCurrentUser()->getId(), ModelGame::getCurrentGame()->getId());
        while ($iterator->hasNext()) {
            /** @var ModelGameArea $gameArea */
            $gameArea = $iterator->next();
            $data['availableGameAreasInPort'][] = array(
                'id_game_area_in_port' => $gameArea->getId(),
                'name' => $gameArea->getName(),
                'number' => $gameArea->getNumber()
            );
        }
        $iterator = ModelArea::iterator(TYPE_SEA);
        while ($iterator->hasNext()) {
            /** @var ModelArea $area */
            $area = $iterator->next();
            $data['availableGameAreasAtSea'][] = array(
                'id_game_area_at_sea' => ModelGameArea::getGameAreaForArea(ModelGame::getCurrentGame()->getId(), $area->getId())->getId(),
                'name' => $area->getName(),
                'number' => $area->getNumber()
            );
        }

        $this->checkFixate($data, PHASE_SETSHIPS);
        $this->checkCurrentPhase($data, PHASE_SETSHIPS);
    }

    private function setShip(array &$data) {
        // get post data and create new move via controller
        try {
            $this->moveController->setNewShip((int)$_POST['unit'], $_POST['name'], (int)$_POST['game_area_in_port'], (int)$_POST['game_area']);
        } catch (ControllerException $ex) {
            $data['errors'] = array(
                'message' => $ex->getMessage()
            );
        } catch (ModelException $ex) {
            $data['errors'] = array(
                'message' => $ex->getMessage()
            );
        }
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

    private function deleteMove(array &$data) {
        try {
            $this->moveController->deleteMove($_POST['delete']);
        } catch (ControllerException $ex) {
            $data['errors'] = array(
                'message' => $ex->getMessage()
            );
        }
    }

}
