<?php
namespace Attack\Controller\Game\Moves;

use Attack\Exceptions\ControllerException;
use Attack\Exceptions\NullPointerException;
use Attack\Model\Game\ModelGameArea;
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

    public function loadAllTradeRoutes() {

    }

    public function simulateAllMoves() {

    }

    public function validateMove(array $steps) {
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
            return true;
        }

        // 2. validate
        // 2.a start + destination area are land and belong to user
        // 2.b neither area in steps is part of another move or trade-route (except traderoute is going to be deleted)
        // 2.c start + destination are not part of existing traderoutes (except these are going to be deleted)
        // 2.d all sea areas contain at least one non-submarine ship from the user
        // 2.e the route is valid
        // 2.f the shortest route is at least 3
        throw new ControllerException('TODO : implement validation');
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

}