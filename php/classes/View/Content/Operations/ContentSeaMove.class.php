<?php
namespace Attack\View\Content\Operations;

use Attack\Exceptions\ControllerException;
use Attack\Exceptions\NullPointerException;
use Attack\Model\Game\ModelGame;
use Attack\Model\Game\ModelGameArea;
use Attack\Model\Units\ModelShip;
use Attack\Model\User\ModelUser;
use Attack\View\Content\Operations\Interfaces\ContentOperation;

class ContentSeaMove extends ContentOperation {

    public function getTemplate() {
        return 'seamove';
    }

    public function run(array &$data) {
        $data['template'] = $this->getTemplate();
        $this->addCurrentGameInfo($data);

        $this->handleInput($data);
        $this->showShips($data);
        $this->showTargetAreas($data);

        if (!$this->checkFixate($data, PHASE_SEAMOVE)) {
            $this->showNewMove($data);
        }
        $this->checkCurrentPhase($data, PHASE_SEAMOVE);
    }

    private function showShips(array &$data) {
        // TODO : implement
    }

    private function showNewMove(array &$data) {
        // TODO : implement
    }

    private function showTargetAreas(array &$data) {
        $destination_areas = [];
        $destination_port_areas = [];
        $iterator = ModelGameArea::iterator(null, ModelGame::getCurrentGame()->getId());
        while ($iterator->hasNext()) {
            /** @var ModelGameArea $gameArea */
            $gameArea = $iterator->next();
            if ($gameArea->getIdType() === TYPE_LAND) {
                $destination_port_areas[] = [
                    'id' => $gameArea->getId(),
                    'name' => $gameArea->getName(),
                    'number' => $gameArea->getNumber()
                ];
            } else {
                $destination_areas[] = [
                    'id' => $gameArea->getId(),
                    'name' => $gameArea->getName(),
                    'number' => $gameArea->getNumber()
                ];
            }
        }
        $data['destinationAreas'] = $destination_areas;
        $data['destinationPortAreas'] = $destination_port_areas;
    }

    private function handleInput(array &$data) {
        if (empty($_POST)) {
            return;
        }

        return;
        // TODO : implement
        $controller = new SeaMoveController(ModelUser::getCurrentUser()->getId(), ModelGame::getCurrentGame()->getId());

        // fixating sea move
        if (isset($_POST['fixate_sea_move'])) {
            $controller->finishMove();
            return;
        }

        // deleting sea move
        if (isset($_POST['delete'])) {
            try {
                $controller->deleteMove((int)$_POST['delete']);
                $data['status'] = array(
                    'message' => 'Seezug gelöscht.'
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

        // creating new sea move
        if (isset($_POST['newmove'])) {
            try {
                if (!isset($_POST['start']) || !isset($_POST['destination'])) {
                    $data['errors'] = array(
                        'message' => 'Missing parameter!'
                    );
                    return;
                }
                // TODO : create sea move
                $data['status'] = array(
                    'message' => 'Seezug erstellt.'
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
