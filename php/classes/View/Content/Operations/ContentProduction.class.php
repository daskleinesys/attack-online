<?php
namespace Attack\View\Content\Operations;

use Attack\Controller\Game\Moves\ProductionController;
use Attack\Exceptions\ControllerException;
use Attack\Exceptions\NullPointerException;
use Attack\Model\Game\ModelGameArea;
use Attack\Model\Game\Moves\ModelProductionMove;
use Attack\Model\Areas\ModelArea;
use Attack\Model\Units\ModelLandUnit;
use Attack\Model\Game\ModelGame;
use Attack\Model\User\ModelUser;
use Attack\Tools\UserViewHelper;
use Attack\View\Content\Operations\Interfaces\ContentOperation;

class ContentProduction extends ContentOperation {

    public function getTemplate() {
        return 'production';
    }

    public function run(array &$data) {
        $data['template'] = $this->getTemplate();
        $this->addCurrentGameInfo($data);

        $this->handleInput($data);
        $this->showMoves($data);
        $this->showCurrentProduction($data);

        if (!$this->checkFixate($data, PHASE_PRODUCTION)) {
            $this->showNewMove($data);
        }
        $this->checkCurrentPhase($data, PHASE_PRODUCTION);
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
                if (!isset($_POST['id_game_area']) || !isset($_POST['id_unit']) || !isset($_POST['count'])) {
                    $data['errors'] = array(
                        'message' => 'Missing Parameters!'
                    );
                    return;
                }
                $units = array();
                $units[(int)$_POST['id_unit']] = (int)$_POST['count'];
                $controller->createProductionMove((int)$_POST['id_game_area'], $units);
                $data['status'] = array(
                    'message' => 'Produktion erstellt.'
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

            $id_game_area = $move->getIdGameArea();
            $gameArea = ModelGameArea::getGameArea((int)$id_game, $id_game_area);
            $area = ModelArea::getArea((int)$gameArea->getIdArea());
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
            $gameArea = ModelGameArea::getGameAreaForArea(ModelGame::getCurrentGame()->getId(), $area->getId());
            $areaViewData = array();
            $areaViewData['id_game_area'] = $gameArea->getId();
            $areaViewData['number'] = $area->getNumber();
            $areaViewData['name'] = $area->getName();
            if ($gameArea->getIdUser() === ModelUser::getCurrentUser()->getId()) {
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
