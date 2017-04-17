<?php
namespace Attack\Controller\Game\Moves;

use Attack\Exceptions\ControllerException;
use Attack\Exceptions\NullPointerException;
use Attack\Model\Game\ModelGameArea;
use Attack\Model\Game\ModelGameShip;
use Attack\Model\Game\ModelTradeRoute;
use Attack\Model\Game\Moves\ModelTradeRouteMove;

class TradeRouteValidator {

    private $id_user;
    private $id_game;
    private $round;

    private $existingTradeRoutes = []; // array(ModelTradeRoute)
    private $newTradeRouteMoves = []; // array(ModelTradeRouteMove)
    private $deleteTradeRouteMoves = []; // array(ModelTradeRouteMove)
    private $tradeRouteAreas = []; // array(int id_game_area)


    /**
     * @param int $id_user
     * @param int $id_game
     * @param int $round
     */
    public function __construct($id_user, $id_game, $round) {
        $this->id_user = $id_user;
        $this->id_game = $id_game;
        $this->round = $round;
    }

    /**
     * validate move according to current traderoute and other moves
     *
     * @param array $steps
     * @param bool $execute_other_moves_first
     * @throws ControllerException
     */
    public function validateMove(array $steps, $execute_other_moves_first = false) {
        $this->loadAllTradeRoutes();
        if ($execute_other_moves_first) {
            $this->simulateAllMoves();
        }

        // 1. check if deletion
        if (count($steps) === 2) {
            // 1.a check if traderoute with same start+destination exists
            $foundTradeRoute = false;
            /** @var ModelTradeRoute $tradeRoute */
            foreach ($this->existingTradeRoutes as $tradeRoute) {
                if ($tradeRoute->getSteps() === $steps) {
                    $foundTradeRoute = true;
                    break;
                }
            }
            if (!$foundTradeRoute) {
                throw new ControllerException('invalid move, no traderoute available');
            }
            // 1.b and is not already deleted
            /** @var ModelTradeRouteMove $tradeRouteMove */
            foreach ($this->deleteTradeRouteMoves as $tradeRouteMove) {
                if ($tradeRouteMove->getSteps() === $steps) {
                    throw new ControllerException('invalid move, traderoute already deleted');
                }
            }
            return;
        }

        // 2. validate
        // 2.a start area is land, belongs to user and isn’t part of another traderoute
        $startArea = ModelGameArea::getGameArea($this->id_game, array_shift($steps));
        if ($startArea->getIdType() !== TYPE_LAND) {
            throw new ControllerException('invalid move, start area is no land area');
        }
        if ($startArea->getIdUser() !== $this->id_user) {
            throw new ControllerException('invalid move, start area doesn\'t belong to user');
        }
        if (in_array($startArea->getId(), $this->tradeRouteAreas)) {
            throw new ControllerException('invalid move, game-area ' . $startArea->getId() . ' already belongs to another traderoute');
        }
        $currentArea = null;
        $previousArea = $startArea;
        while ($id_game_area = array_shift($steps)) {
            // 2.b every step adjacent to previous
            $currentArea = ModelGameArea::getGameArea($this->id_game, $id_game_area);
            if (!in_array($currentArea->getId(), $previousArea->getAdjacentGameAreas())) {
                throw new ControllerException('invalid move, game-area ' . $currentArea->getId() . ' not adjacent to game-area ' . $previousArea->getId());
            }
            if (empty($steps)) {
                break;
            }
            // 2.c every step is sea, not part of another traderoute and has a ship of the user
            if ($currentArea->getIdType() !== TYPE_SEA) {
                throw new ControllerException('invalid move, game-area ' . $currentArea->getId() . ' is no sea area');
            }
            if (in_array($currentArea->getId(), $this->tradeRouteAreas)) {
                throw new ControllerException('invalid move, game-area ' . $previousArea->getId() . ' already belongs to another traderoute');
            }
            if (!$this->gameAreaHasShip($currentArea)) {
                throw new ControllerException('invalid move, game-area ' . $previousArea->getId() . ' has no valid ships');
            }
            $previousArea = $currentArea;
        }
        // 2.d destination area is land, belongs to user and isn’t part of another traderoute
        if ($currentArea->getIdType() !== TYPE_LAND) {
            throw new ControllerException('invalid move, destination area is no land area');
        }
        if ($currentArea->getIdUser() !== $this->id_user) {
            throw new ControllerException('invalid move, destination area doesn\'t belong to user');
        }
        if (in_array($currentArea->getId(), $this->tradeRouteAreas)) {
            throw new ControllerException('invalid move, game-area ' . $previousArea->getId() . ' already belongs to another traderoute');
        }
        // 2.e the shortest route is at least 3
        if (self::checkShortestRoute($startArea, $currentArea) < TRADEROUTE_MIN_START_VALUE) {
            throw new ControllerException('invalid move, traderoute too short');
        }
        return;
    }


    /**
     * calculates the shortest sea-route between two areas
     * returns the number of sea-areas crossed
     *
     * @param ModelGameArea $startArea
     * @param ModelGameArea $destinationArea
     * @return int
     * @throws ControllerException
     * @throws NullPointerException
     */
    public static function checkShortestRoute(ModelGameArea $startArea, ModelGameArea $destinationArea) {
        if ($startArea->getIdType() !== TYPE_LAND || $destinationArea->getIdType() !== TYPE_LAND) {
            throw new ControllerException('illegal game areas for traderoute connection: ' . $startArea->getId() . ' and ' . $destinationArea->getId());
        }
        $id_game = $startArea->getIdGame();
        $visited = [$startArea->getId()];
        $next = [];
        $current = [];
        $distance = 0;

        foreach ($startArea->getAdjacentGameAreas() as $id_game_area) {
            $gameArea = ModelGameArea::getGameArea($id_game, $id_game_area);
            if ($gameArea->getIdType() !== TYPE_SEA) {
                continue;
            }
            $visited[] = $id_game_area;
            $current[] = $id_game_area;
        }

        while (!empty($current)) {
            $id_game_area = array_shift($current);
            if ($id_game_area === $destinationArea->getId()) {
                return $distance;
            }
            $gameArea = ModelGameArea::getGameArea($id_game, $id_game_area);
            if ($gameArea->getIdType() === TYPE_SEA) {
                foreach ($gameArea->getAdjacentGameAreas() as $id_next_game_area) {
                    if (!in_array($id_next_game_area, $visited)) {
                        $visited[] = $id_next_game_area;
                        $next[] = $id_next_game_area;
                    }
                }
            }
            if (empty($current)) {
                $current = $next;
                $next = [];
                ++$distance;
            }
        }
        throw new ControllerException('no route found between game areas ' . $startArea->getId() . ' and ' . $destinationArea->getId());
    }

    private function loadAllTradeRoutes() {
        $tradeRoutes = ModelTradeRoute::iterator($this->id_user, $this->id_game);
        while (($tradeRoutes->hasNext())) {
            /** @var ModelTradeRoute $tradeRoute */
            $tradeRoute = $tradeRoutes->next();
            foreach ($tradeRoute->getSteps() as $id_game_area) {
                $this->tradeRouteAreas[] = $id_game_area;
            }
            $this->existingTradeRoutes[] = $tradeRoute;
        }
    }

    private function simulateAllMoves() {
        $moves = ModelTradeRouteMove::iterator($this->id_user, $this->id_game, $this->round);
        while ($moves->hasNext()) {
            /** @var ModelTradeRouteMove $move */
            $move = $moves->next();
            $steps = $move->getSteps();
            if (count($steps) === 2) {
                $traderoutes = ModelTradeRoute::iterator($this->id_user, $this->id_game);
                while ($traderoutes->hasNext()) {
                    /** @var ModelTradeRoute $traderoute */
                    $traderoute = $traderoutes->next();
                    if ($traderoute->getSteps()[0] === $steps[0]) {
                        $this->tradeRouteAreas = array_filter($this->tradeRouteAreas, function ($id_game_area) use ($traderoute) {
                            if (in_array($id_game_area, $traderoute->getSteps())) {
                                return false;
                            }
                            return true;
                        });
                        break;
                    }
                }
                $this->deleteTradeRouteMoves[] = $move;
            } else {
                foreach ($steps as $id_game_area) {
                    $this->tradeRouteAreas[] = $id_game_area;
                }
                $this->newTradeRouteMoves[] = $move;
            }
        }
    }

    private function gameAreaHasShip(ModelGameArea $gameArea) {
        $ships = ModelGameShip::getShipsInAreaNotInPortByUser($this->id_user, $this->id_game, $gameArea->getId());
        while ($ships->hasNext()) {
            /** @var ModelGameShip $ship */
            $ship = $ships->next();
            if ($ship->getIdUnit() === TYPE_SUBMARINE) {
                continue;
            }
            return true;
        }
        return false;
    }

}
