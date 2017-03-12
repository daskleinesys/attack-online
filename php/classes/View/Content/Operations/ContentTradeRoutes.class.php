<?php
namespace Attack\View\Content\Operations;

use Attack\Controller\Game\Moves\TradeRoutesController;
use Attack\Model\Game\ModelGame;
use Attack\Model\Game\ModelGameArea;
use Attack\Model\Game\ModelTradeRoute;
use Attack\Model\Game\Moves\ModelTradeRouteMove;
use Attack\Model\User\ModelUser;
use Attack\View\Content\Operations\Interfaces\ContentOperation;

class ContentTradeRoutes extends ContentOperation {

    /**
     * @var TradeRoutesController
     */
    private $controller;

    public function getTemplate() {
        return 'traderoutes';
    }

    public function run(array &$data) {
        $data['template'] = $this->getTemplate();
        $this->addCurrentGameInfo($data);
        $this->controller = new TradeRoutesController(ModelUser::getCurrentUser()->getId(), ModelGame::getCurrentGame()->getId());

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

        // fixating sea move
        if (isset($_POST['fixate_traderoutes'])) {
            $this->controller->finishMove();
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
                'current_pp' => $tradeRoute->getCurrentValue() * TRADEROUTE_PP_MULTIPLIER,
                'max_value' => $tradeRoute->getMaxValue(),
                'max_pp' => $tradeRoute->getMaxValue() * TRADEROUTE_PP_MULTIPLIER,
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
        $game = ModelGame::getCurrentGame();
        $round = $game->getRound();
        $phase = $game->getIdPhase();
        if ($phase > PHASE_TRADEROUTES) {
            ++$round;
        }

        $newTradeRoutesViewData = [];
        $iterator = ModelTradeRouteMove::iterator(ModelUser::getCurrentUser()->getId(), $game->getId(), $round);
        while ($iterator->hasNext()) {
            /** @var ModelTradeRouteMove $move */
            $move = $iterator->next();
            $steps = $move->getSteps();
            $traderoute_max_value = $this->controller->checkShortestRoute($steps[0], end($steps)) * TRADEROUTE_MAX_VALUE_MULTIPLIER;
            $traderoute_max_pp = $traderoute_max_value * TRADEROUTE_PP_MULTIPLIER;
            $moveViewData = [
                'id' => $move->getId(),
                'traderoute_max_value' => $traderoute_max_value,
                'traderoute_max_pp' => $traderoute_max_pp,
                'areas' => []
            ];
            foreach ($steps as $step => $id_game_area) {
                $gameArea = ModelGameArea::getGameArea($game->getId(), $id_game_area);
                $moveViewData['areas'][] = [
                    'number' => $gameArea->getNumber(),
                    'name' => $gameArea->getName()
                ];
            }
            $newTradeRoutesViewData[] = $moveViewData;
        }
        $data['new_traderoute_moves'] = $newTradeRoutesViewData;
    }

}
