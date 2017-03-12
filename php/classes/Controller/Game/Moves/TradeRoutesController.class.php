<?php
namespace Attack\Controller\Game\Moves;

use Attack\Controller\Interfaces\PhaseController;
use Attack\Exceptions\ControllerException;
use Attack\Exceptions\NullPointerException;
use Attack\Model\Game\ModelGameArea;

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
        // TODO : implement
        throw new ControllerException('TODO : implement');
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