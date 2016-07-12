<?php
namespace AttOn\View\Content\Operations;

use AttOn\Controller\Game\InGame\SetShipsController;
use AttOn\Exceptions\ControllerException;
use AttOn\Exceptions\ModelException;
use AttOn\Model\Atton\InGame\ModelGameArea;
use AttOn\Model\Atton\InGame\ModelInGameShip;
use AttOn\Model\Atton\InGame\Moves\ModelSetShipsMove;
use AttOn\Model\Atton\ModelArea;
use AttOn\Model\Atton\ModelShip;
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
        if (isset($_POST['delete'])) {
            $this->deleteMove($data);
        }

        // show already set ships
        $data['currentShips'] = array();
        $iterator = ModelSetShipsMove::getSetShipMovesForUser(ModelUser::getCurrentUser()->getId(), ModelGame::getCurrentGame()->getId());
        while ($iterator->hasNext()) {
            /** @var ModelSetShipsMove $move */
            $move = $iterator->next();
            $zShip = ModelInGameShip::getShipById(ModelGame::getCurrentGame()->getId(), $move->getIdZunit());
            $id_ship = $zShip->getIdUnit();
            $ship = ModelShip::getModelById($id_ship);
            $zAreaInPort = ModelGameArea::getGameArea(ModelGame::getCurrentGame()->getId(), $move->getIdZareaInPort());
            $zAreaAtSea = ModelGameArea::getGameArea(ModelGame::getCurrentGame()->getId(), $move->getIdZarea());
            $data['currentShips'][] = array(
                'id' => $move->getId(),
                'ship_type' => $ship->getName(),
                'ship_name' => $zShip->getName(),
                'zarea_in_port' => $zAreaInPort->getName() . ' ' . $zAreaInPort->getNumber(),
                'zarea_at_sea' => $zAreaAtSea->getName() . ' ' . $zAreaAtSea->getNumber()
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
        $data['availableZAreasInPort'] = array();
        $data['availableZAreasAtSea'] = array();
        $iterator = ModelGameArea::iterator(ModelUser::getCurrentUser()->getId(), ModelGame::getCurrentGame()->getId());
        while ($iterator->hasNext()) {
            /** @var ModelGameArea $zArea */
            $zArea = $iterator->next();
            $data['availableZAreasInPort'][] = array(
                'id_zarea_in_port' => $zArea->getId(),
                'name' => $zArea->getName(),
                'number' => $zArea->getNumber()
            );
        }
        $iterator = ModelArea::iterator(TYPE_SEA);
        while ($iterator->hasNext()) {
            /** @var ModelArea $area */
            $area = $iterator->next();
            $data['availableZAreasAtSea'][] = array(
                'id_zarea_at_sea' => ModelGameArea::getGameAreaForArea(ModelGame::getCurrentGame()->getId(), $area->getId())->getId(),
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
            $this->moveController->setNewShip($_POST['unit'], $_POST['name'], $_POST['zarea_in_port'], $_POST['zarea']);
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
