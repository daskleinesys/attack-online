<?php
namespace Attack\View\Content\Operations;

use Attack\Controller\Game\Moves\TradeRoutesController;
use Attack\Model\Game\ModelGame;
use Attack\Model\Game\ModelGameArea;
use Attack\Model\Game\ModelTradeRoute;
use Attack\Model\User\ModelUser;
use Attack\View\Content\Operations\Interfaces\ContentOperation;

class ContentTradeRoutes extends ContentOperation {

    public function getTemplate() {
        return 'traderoutes';
    }

    public function run(array &$data) {
        $data['template'] = $this->getTemplate();
        $this->addCurrentGameInfo($data);

        if (!$this->checkFixate($data, PHASE_TRADEROUTES)) {
            $this->handleInput($data);
        }

        $this->showTradeRoutes($data);
        $this->showCreateMoves($data);

        $this->checkCurrentPhase($data, PHASE_TRADEROUTES);
    }

    private function handleInput(array &$data) {
        if (empty($_POST)) {
            return;
        }
        $controller = new TradeRoutesController(ModelUser::getCurrentUser()->getId(), ModelGame::getCurrentGame()->getId());

        // fixating sea move
        if (isset($_POST['fixate_traderoutes'])) {
            $controller->finishMove();
            $this->checkFixate($data, PHASE_TRADEROUTES);
            return;
        }
    }

    private function showTradeRoutes(array &$data) {
        $tradeRoutesViewData = [];
        $iterator = ModelTradeRoute::iterator(ModelUser::getCurrentUser()->getId(), ModelGame::getCurrentGame()->getId());
        while ($iterator->hasNext()) {
            /** @var ModelTradeRoute $tradeRoute */
            $tradeRoute = $iterator->next();
            $tradeRouteViewData = [
                'id' => $tradeRoute->getId(),
                'current_value' => $tradeRoute->getCurrentValue(),
                'max_value' => $tradeRoute->getMaxValue(),
                'areas' => []
            ];
            foreach ($tradeRoute->getSteps() as $step => $id_game_area) {
                $gameArea = ModelGameArea::getGameArea(ModelGame::getCurrentGame()->getId(), $id_game_area);
                $tradeRouteViewData['areas'][] = [
                    'number' => $gameArea->getNumber(),
                    'name' => $gameArea->getName()
                ];
            }
            $tradeRoutesViewData[] = $tradeRouteViewData;
        }
        $data['active_traderoutes'] = $tradeRoutesViewData;
    }

    private function showCreateMoves(array &$data) {
        $newTradeRoutesViewData = [];
        // TODO : implement traderoutemove
        return;
        $iterator = ModelTradeRoute::iterator(ModelUser::getCurrentUser()->getId(), ModelGame::getCurrentGame()->getId());
        while ($iterator->hasNext()) {
            /** @var ModelTradeRoute $tradeRoute */
            $tradeRoute = $iterator->next();
            $tradeRouteViewData = [
                'id' => $tradeRoute->getId(),
                'current_value' => $tradeRoute->getCurrentValue(),
                'max_value' => $tradeRoute->getMaxValue(),
                'areas' => []
            ];
            foreach ($tradeRoute->getSteps() as $step => $id_game_area) {
                $gameArea = ModelGameArea::getGameArea(ModelGame::getCurrentGame()->getId(), $id_game_area);
                $tradeRouteViewData['areas'][] = [
                    'number' => $gameArea->getNumber(),
                    'name' => $gameArea->getName()
                ];
            }
            $newTradeRoutesViewData[] = $tradeRouteViewData;
        }
        $data['new_traderoute_moves'] = $newTradeRoutesViewData;
    }

}
