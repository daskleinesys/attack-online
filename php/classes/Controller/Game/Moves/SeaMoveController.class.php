<?php
namespace Attack\Controller\Game\Moves;

use Attack\Controller\Interfaces\PhaseController;
use Attack\Exceptions\ControllerException;
use Attack\Exceptions\NullPointerException;
use Attack\Model\Game\ModelGame;
use Attack\Model\Game\ModelGameArea;
use Attack\Model\Game\ModelGameShip;
use Attack\Model\Game\Moves\ModelSeaMove;

class SeaMoveController extends PhaseController {

    /**
     * @param int $id_user - id of the user accessing the moderation actions
     * @param int $id_game - id of currently selected game
     */
    public function __construct($id_user, $id_game) {
        parent::__construct((int)$id_user, (int)$id_game, PHASE_SEAMOVE);
    }

    /**
     * fixates the move if no error occured
     *
     * @return void
     */
    public function finishMove() {
        $this->fixatePhase(true);
    }


    /**
     * check if user owns the ship
     * check if ship has already move for current phase
     * check if target area is reachable
     * if valid -> create or update move and return it or delete move if target areas are same as current ship area
     *
     * @param ModelGameShip $ship
     * @param int $id_target_area
     * @param int $id_target_port_area
     * @return bool
     * @throws ControllerException
     */
    public function setMoveForShip(ModelGameShip $ship, $id_target_area, $id_target_port_area) {
        // check if already fixated
        if ($this->checkIfDone()) {
            throw new ControllerException('seamove already fixated');
        }
        if ($ship->getIdUser() !== $this->id_user) {
            throw new ControllerException('invalid action - user doesnt own this ship: ' . $ship->getName());
        }

        $game = ModelGame::getGame($this->id_game);
        $round = $game->getRound();
        $phase = $game->getIdPhase();
        if ($phase > PHASE_SEAMOVE) {
            ++$round;
        }

        // try to load move for ship
        try {
            $move = ModelSeaMove::getByShipId($this->id_game, $round, $ship->getId());
        } catch (NullPointerException $ex) {
            $move = null;
        }

        // check if move is necessary
        if (
            $id_target_area === $ship->getIdGameArea() &&
            $id_target_port_area === $ship->getIdGameAreaInPort()
        ) {
            if ($move) {
                ModelSeaMove::delete($move);
            }
            return true;
        }

        // check if move has changed
        if ($move) {
            $steps = $move->getSteps();
            if (
                $steps[1][0] === $ship->getIdGameArea() &&
                $steps[1][1] === $ship->getIdGameAreaInPort() &&
                $steps[2][0] === $id_target_area &&
                $steps[2][1] === $id_target_port_area
            ) {
                return true;
            }
        }

        // validate move
        $this->validateShipMovement($ship, $id_target_area, $id_target_port_area);

        // update move
        if ($move) {
            $move->setSteps([
                1 => [$ship->getIdGameArea(), $ship->getIdGameAreaInPort()],
                2 => [$id_target_area, $id_target_port_area]
            ]);
            return true;
        }

        ModelSeaMove::create($this->id_user, $this->id_game, $round, [
            1 => [$ship->getIdGameArea(), $ship->getIdGameAreaInPort()],
            2 => [$id_target_area, $id_target_port_area]
        ], $ship->getId());
        return true;
    }

    /**
     * validate if given move is possible
     *
     * @param ModelSeaMove $move
     * @return bool
     * @throws ControllerException
     */
    public function validateMove(ModelSeaMove $move) {
        $steps = $move->getSteps();
        $id_game_ship = $move->getIdGameShip();
        $ship = ModelGameShip::getShipById($this->id_game, $id_game_ship);
        return $this->validateShipMovement($ship, $steps[2][0], $steps[2][1]);
    }

    private function validateShipMovement(ModelGameShip $ship, $id_target_area, $id_target_port_area) {
        $id_start_area = $ship->getIdGameArea();
        $id_start_port_area = $ship->getIdGameAreaInPort();

        // 1. if neither area nor port-area changed OR both changed -> invalid
        if ($id_start_area === $id_target_area && $id_start_port_area === $id_target_port_area) {
            throw new ControllerException("same sea and port area for sea move - " . $ship->getName());
        }
        if ($id_start_area !== $id_target_area && $id_start_port_area !== $id_target_port_area) {
            throw new ControllerException('different sea and port area for sea move - ' . $ship->getName());
        }

        // 2. if area changed: if adjacent -> valid; if not -> invalid
        if ($id_start_area !== $id_target_area) {
            if (!in_array($id_target_area, ModelGameArea::getGameArea($this->id_game, $id_start_area)->getAdjacentGameAreas())) {
                throw new ControllerException("sea areas $id_start_area and $id_target_area not adjacents for sea move - " . $ship->getName());
            } else {
                return true;
            }
        }

        // 3. if target-port-area is NO_AREA -> valid
        if ($id_target_port_area === NO_AREA) {
            return true;
        }

        // 4. if target-port-area doesn't belong to user -> invalid
        if (ModelGameArea::getGameArea($this->id_game, $id_target_port_area)->getIdUser() !== $this->id_user) {
            throw new ControllerException('target area doesn\'t belong to current user - ' . $ship->getName());
        }

        // 5. if target-port-area not adjacent to target-area -> invalid
        if (!in_array($id_target_port_area, ModelGameArea::getGameArea($this->id_game, $id_target_area)->getAdjacentGameAreas())) {
            throw new ControllerException('port areas not adjacents for sea move - ' . $ship->getName());
        }

        // 6. if start-port-area is NO_AREA -> valid
        if ($id_start_port_area === NO_AREA) {
            return true;
        }

        // 7. if port-area not adjacent -> invalid
        if (!in_array($id_target_port_area, ModelGameArea::getGameArea($this->id_game, $id_start_port_area)->getAdjacentGameAreas())) {
            throw new ControllerException('port areas not adjacents for sea move - ' . $ship->getName());
        }

        return true;
    }

}