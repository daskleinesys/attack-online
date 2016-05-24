<?php
namespace AttOn\View\Content\Operations;

use AttOn\Controller\Game\InGame\ProductionController;
use AttOn\Exceptions\ControllerException;
use AttOn\Exceptions\NullPointerException;
use AttOn\Model\Atton\InGame\ModelGameArea;
use AttOn\Model\Atton\InGame\Moves\ModelProductionMove;
use AttOn\Model\Atton\ModelArea;
use AttOn\Model\Atton\ModelLandUnit;
use AttOn\Model\Game\ModelGame;
use AttOn\Model\User\ModelUser;
use AttOn\Tools\UserViewHelper;
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

        $controller = new ProductionController(ModelUser::getCurrentUser()->getId(), ModelGame::getCurrentGame()->getId());

        // 1. check fixate
        if (isset($_POST['fixate_production'])) {
            $controller->finishMove();
            return;
        }

        // 2. delete production if necessary
        if (isset($_POST['delete'])) {
            try {
                $controller->deleteProductionMove(intval($_POST['delete']));
                $data['status'] = array(
                    'message' => 'Produktionszug gelöscht.'
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

        // 3 create new production move (incl validation)
        if (isset($_POST['newmove'])) {
            try {
                if (!isset($_POST['id_zarea']) || !isset($_POST['id_unit']) || !isset($_POST['count'])) {
                    $data['errors'] = array(
                        'message' => 'Missing Parameters!'
                    );
                    return;
                }
                $units = array();
                $units[(int)$_POST['id_unit']] = (int)$_POST['count'];
                $controller->createProductionMove((int)$_POST['id_zarea'], $units);
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

    private function showMoves(array &$data) {
        // show moves
        $costsSum = 0;
        $game = ModelGame::getCurrentGame();
        $id_game = $game->getId();
        $round = $game->getRound();
        $phase = $game->getIdPhase();
        if ($phase > PHASE_PRODUCTION) {
            ++$round;
        }

        $moves = ModelProductionMove::iterator(ModelUser::getCurrentUser()->getId(), $id_game, $round);
        $movesViewData = array();
        while ($moves->hasNext()) {
            /* @var $move ModelProductionMove */
            $move = $moves->next();
            $moveViewData = array();
            $moveViewData['id'] = $move->getIdMove();

            $id_zarea = $move->getIdZArea();
            $zArea = ModelGameArea::getGameArea((int)$id_game, $id_zarea);
            $area = ModelArea::getArea((int)$zArea->getIdArea());
            $moveViewData['area'] = array(
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
                if (!isset($units[$id_unit]) || $units[$id_unit] <= 0) {
                    continue;
                }
                $unitsViewData['name'] = $unit->getName();
                $unitsViewData['count'] = $units[$id_unit];
                $unitsViewData['cost'] = (int)$unit->getPrice() * $units[$id_unit];
                $costsSum += $unitsViewData['cost'];
                break;
            }
            $moveViewData['units'] = $unitsViewData;
            $movesViewData[] = $moveViewData;
        }
        $data['moves'] = $movesViewData;
        $data['costsSum'] = $costsSum;
    }

    private function showCurrentProduction(array &$data) {
        $data['production'] = UserViewHelper::getCurrentProductionForUserInGame(ModelUser::getCurrentUser()->getId(), ModelGame::getCurrentGame()->getId());
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
