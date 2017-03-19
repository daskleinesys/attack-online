<?php
namespace Attack\View\Content\Operations;

use Attack\Controller\Game\Moves\TradeRoutesController;
use Attack\Controller\Game\Moves\TradeRouteValidator;
use Attack\Exceptions\ControllerException;
use Attack\Exceptions\NullPointerException;
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
    private $id_user;
    private $id_game;
    private $round;

    public function getTemplate() {
        return 'traderoutes';
    }

    public function run(array &$data) {
        $data['template'] = $this->getTemplate();
        $this->addCurrentGameInfo($data);

        $game = ModelGame::getCurrentGame();
        $this->id_user = ModelUser::getCurrentUser()->getId();
        $this->id_game = $game->getId();
        $this->controller = new TradeRoutesController($this->id_user, $this->id_game);
        $this->round = $game->getRound();
        $phase = $game->getIdPhase();
        if ($phase > PHASE_TRADEROUTES) {
            ++$this->round;
        }

        if (!$this->checkFixate($data, PHASE_TRADEROUTES)) {
            try {
                $this->handleInput($data);
            } catch (\Exception $ex) {
                $data['errors'] = [
                    'message' => $ex->getMessage()
                ];
            }
        }

        $this->addNewTradeRouteNextAreaOption($data);
        $this->showTradeRoutes($data);
        $this->showCreateMoves($data);

        $this->checkCurrentPhase($data, PHASE_TRADEROUTES);
    }

    private function handleInput(array &$data) {
        // delete traderoute
        if (isset($_POST['delete_traderoute'])) {
            throw new \Exception('TODO : implement traderoute deletion');
        }

        // delete traderoute move
        if (isset($_POST['delete_traderoute_move'])) {
            try {
                $this->controller->deleteMove((int)$_POST['delete_traderoute_move']);
                $data['status'] = array(
                    'message' => 'Zug gelöscht.'
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

        // abort new traderoute
        if (isset($_POST['new_traderoute_abort'])) {
            unset($_POST['new_traderoute_game_areas']);
            return;
        }

        // add new field for new traderoute
        if (isset($_POST['new_traderoute_add_area'])) {
            $this->addAreaToTradeRoute($data);
        }

        // new traderoute finished - create move
        if (isset($_POST['new_traderoute_finish'])) {
            $this->finishNewTradeRoute($data);
        }

        // fixating sea move
        if (isset($_POST['fixate_traderoutes'])) {
            $this->controller->finishMove();
            $this->checkFixate($data, PHASE_TRADEROUTES);
            return;
        }
    }

    private function addAreaToTradeRoute(array &$data) {
        $viewData = [];
        if (isset($_POST['new_traderoute_game_areas'])) {
            foreach ($_POST['new_traderoute_game_areas'] as $id_game_area) {
                $gameArea = ModelGameArea::getGameArea($this->id_game, (int)$id_game_area);
                $viewData[] = [
                    'id' => $gameArea->getId(),
                    'number' => $gameArea->getNumber(),
                    'name' => $gameArea->getName()
                ];
            }
        }
        if (isset($_POST['new_traderoute_next_area_options'])) {
            $gameArea = ModelGameArea::getGameArea($this->id_game, (int)$_POST['new_traderoute_next_area_options']);
            $viewData[] = [
                'id' => $gameArea->getId(),
                'number' => $gameArea->getNumber(),
                'name' => $gameArea->getName()
            ];
            if (count($viewData) > 1 && $gameArea->getIdType() === TYPE_LAND) {
                $data['new_traderoute_finished'] = true;
            }
        }
        if (!empty($viewData)) {
            $data['new_traderoute_game_areas'] = $viewData;
        }
    }

    private function finishNewTradeRoute(array &$data) {
        if (!isset($_POST['new_traderoute_game_areas'])) {
            throw new ControllerException('unable to create traderoute, missing areas');
        }
        $steps = [];
        foreach ($_POST['new_traderoute_game_areas'] as $id_game_area) {
            $steps[] = (int)$id_game_area;
        }
        $this->controller->create($this->id_user, $this->id_game, $this->round, $steps);
        $data['status'] = [
            'message' => 'new traderoute added'
        ];
    }

    private function addNewTradeRouteNextAreaOption(array &$data) {
        $viewData = [];
        if (isset($data['new_traderoute_game_areas'])) {
            $id_game_area = end($data['new_traderoute_game_areas'])['id'];
            $areas = ModelGameArea::getGameArea($this->id_game, $id_game_area)->getAdjacentGameAreas();
            foreach ($areas as $id_adjacent_game_area) {
                $gameArea = ModelGameArea::getGameArea($this->id_game, $id_adjacent_game_area);
                if (
                    count($data['new_traderoute_game_areas']) === 1 && $gameArea->getIdType() === TYPE_LAND
                    ||
                    $gameArea->getIdType() === TYPE_LAND && $gameArea->getIdUser() !== $this->id_user
                ) {
                    continue;
                }
                $viewData[] = [
                    'id' => $gameArea->getId(),
                    'number' => $gameArea->getNumber(),
                    'name' => $gameArea->getName()
                ];
            }
        } else {
            $iterator = ModelGameArea::iterator($this->id_user, $this->id_game);
            while ($iterator->hasNext()) {
                /** @var ModelGameArea $gameArea */
                $gameArea = $iterator->next();
                if ($gameArea->getIdType() !== TYPE_LAND) {
                    continue;
                }
                $viewData[] = [
                    'id' => $gameArea->getId(),
                    'number' => $gameArea->getNumber(),
                    'name' => $gameArea->getName()
                ];
            }
        }
        $data['new_traderoute_next_area_options'] = $viewData;
    }

    private function showTradeRoutes(array &$data) {
        $tradeRoutesViewData = [];
        $iterator = ModelTradeRoute::iterator($this->id_user, $this->id_game);
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
                $gameArea = ModelGameArea::getGameArea($this->id_game, $id_game_area);
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
        $iterator = ModelTradeRouteMove::iterator($this->id_user, $this->id_game, $this->round);
        while ($iterator->hasNext()) {
            /** @var ModelTradeRouteMove $move */
            $move = $iterator->next();
            $steps = $move->getSteps();
            $startArea = ModelGameArea::getGameArea($this->id_game, $steps[0]);
            $destinationArea = ModelGameArea::getGameArea($this->id_game, end($steps));
            $traderoute_max_value = TradeRouteValidator::checkShortestRoute($startArea, $destinationArea) * TRADEROUTE_MAX_VALUE_MULTIPLIER;
            $traderoute_max_pp = $traderoute_max_value * TRADEROUTE_PP_MULTIPLIER;
            $moveViewData = [
                'id' => $move->getId(),
                'traderoute_max_value' => $traderoute_max_value,
                'traderoute_max_pp' => $traderoute_max_pp,
                'areas' => []
            ];
            foreach ($steps as $step => $id_game_area) {
                $gameArea = ModelGameArea::getGameArea($this->id_game, $id_game_area);
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
