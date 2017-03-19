<?php
namespace Attack\Controller\Game\Moves;

use Attack\Controller\Interfaces\PhaseController;
use Attack\Exceptions\ControllerException;
use Attack\Exceptions\NullPointerException;
use Attack\Model\Game\ModelGame;
use Attack\Model\Game\ModelGameArea;
use Attack\Model\Game\Moves\ModelTradeRouteMove;

class TradeRoutesController extends PhaseController {

    /**
     * @param int $id_user - id of the user accessing the moderation actions
     * @param int $id_game - id of currently selected game
     */
    public function __construct($id_user, $id_game) {
        parent::__construct((int)$id_user, (int)$id_game, PHASE_TRADEROUTES);
    }

    /**
     * fixates the move if no error occured
     *
     * @return void
     */
    public function finishMove() {
        $this->fixatePhase(true);
    }

    public function create($id_user, $id_game, $round, $steps) {
        // 1. check if deletion
        // 1.a if deletion and no other deletion for this traderoute exists, create move + return
        throw new ControllerException('TODO : implement traderoute deletion');

        // 2. validate
        // 2.a start + destination area are land and belong to user
        // 2.b neither area in steps is part of another move (except start+destination in deletion moves)
        // 2.c start + destination are not part of existing traderoutes (except these are going to be deleted)
        // 2.d all sea areas contain at least one non-submarine ship from the user
        // 2.e the route is valid
        // 2.f the shortest route is at least 3
        throw new ControllerException('TODO : implement validation');

        // 3. create move model
        ModelTradeRouteMove::create($id_user, $id_game, $round, $steps);
    }

    /**
     * checks if the user is allowed to delete this move
     * then deletes it from database
     *
     * @param $id_move
     * @throws ControllerException
     * @throws NullPointerException
     */
    public function deleteMove($id_move) {
        // check if move exists
        $move = ModelTradeRouteMove::getById($id_move);

        // check if move is from user
        if ($this->id_user !== $move->getIdUser()) {
            throw new ControllerException('Unable to delete move from another user.');
        }
        // check if already fixated
        if ($this->checkIfDone()) {
            throw new ControllerException('Current phase already finished.');
        }
        // check if processing
        $game = ModelGame::getGame($this->id_game);
        if ($game->checkProcessing()) {
            throw new ControllerException('Unable to delete moves at this moment as the game-logic is currently processing.');
        }
        // check if move is from the current round
        $move_round = $move->getRound();
        $round = $game->getRound();
        $phase = $game->getIdPhase();
        if ($phase > PHASE_TRADEROUTES) {
            ++$round;
        }
        if ($round != $move_round) {
            throw new ControllerException('Unable to delete move as it is not from the correct round.');
        }

        // delete move
        ModelTradeRouteMove::delete($move);

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
    public function checkShortestRoute(ModelGameArea $startArea, ModelGameArea $destinationArea) {
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