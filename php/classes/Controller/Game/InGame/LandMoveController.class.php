<?php
namespace Attack\Controller\Game\InGame;

use Attack\Controller\Interfaces\PhaseController;
use Attack\Exceptions\ControllerException;
use Attack\Exceptions\NullPointerException;
use Attack\Model\Atton\InGame\ModelGameArea;
use Attack\Model\Atton\InGame\ModelInGameLandUnit;
use Attack\Model\Atton\InGame\Moves\ModelLandMove;
use Attack\Model\Atton\ModelLandUnit;
use Attack\Model\Game\ModelGame;

class LandMoveController extends PhaseController {

    /**
     * @param int $id_user - id of the user accessing the moderation actions
     * @param int $id_game - id of currently selected game
     * @return LandMoveController
     */
    public function __construct($id_user, $id_game) {
        parent::__construct((int)$id_user, (int)$id_game, PHASE_LANDMOVE);
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
     * delete land move for user
     *
     * @param int $id_move
     * @throws NullPointerException
     * @throws ControllerException
     * @return void
     */
    public function deleteLandMove($id_move) {
        $id_move = intval($id_move);

        // check if move exists
        $move = ModelLandMove::getLandMove($this->id_game, $id_move);
        // check if move is from user
        if ($this->id_user !== $move->getIdUser()) {
            throw new ControllerException('Unable to delete move from another user.');
        }
        // check if already fixated
        if ($this->checkIfDone()) {
            throw new ControllerException('LandMove already finished.');
        }
        // check if processing
        $game = ModelGame::getGame($this->id_game);
        if ($game->checkProcessing()) {
            throw new ControllerException('Unable to delete moves at this moment as the game-logic is currently processing.');
        }
        // check if move is a landmove from the current round
        $move_round = $move->getRound();
        $round = $game->getRound();
        $phase = $game->getIdPhase();
        if ($phase > PHASE_LANDMOVE) {
            ++$round;
        }
        if ($round != $move_round) {
            throw new ControllerException('Unable to delete move as it is not from the correct round.');
        }

        // delete move
        $move->flagMoveDeleted();

        return;
    }

    /**
     * create land move for user
     *
     * @param int $start (id_zarea)
     * @param int $destination (id_zarea)
     * @param array $units (int $id_unit -> $int count)
     * @throws NullPointerException
     * @throws ControllerException
     * @return ModelLandMove
     */
    public function createLandMove($start, $destination, array $units) {
        $start = intval($start);
        $destination = intval($destination);

        // check if already fixated
        if ($this->checkIfDone()) {
            throw new ControllerException('LandMove already finished.');
        }

        // check if processing
        $game = ModelGame::getGame($this->id_game);
        if ($game->checkProcessing()) {
            throw new ControllerException('Unable to create moves at this moment as the game-logic is currently processing.');
        }
        // check if countries picked
        if ($start === 0 || $destination === 0) {
            throw new ControllerException('Choose a start and destination country.');
        }
        // check for units
        $unit_count = 0;
        foreach ($units as $count) {
            if ($count < 0) {
                throw new ControllerException('No negative unit numbers allowed.');
            }
            $unit_count += $count;
        }
        if ($unit_count === 0) {
            throw new ControllerException('Choose at least one unit.');
        }
        // check if start and destination is the same
        if ($start === $destination) {
            throw new ControllerException('Units have to move at least 1 country.');
        }

        $steps = array();
        $steps[1] = $start;
        $steps[2] = $destination;

        $round = (int)$game->getRound();
        $phase = (int)$game->getIdPhase();
        if ($phase > PHASE_LANDMOVE) {
            ++$round;
        }

        $this->validateLandMove(0, $round, $steps, $units);
        return ModelLandMove::createLandMove($this->id_user, $this->id_game, $round, $steps, $units);
    }

    /**
     * checks if this move is valid, throws exception if not valid
     *
     * @param int $id_move
     * @throws NullPointerException
     * @throws ControllerException
     * @return boolean
     */
    public function validateLandMoveByid($id_move) {
        $id_move = intval($id_move);

        // check if move is not already over
        $game = ModelGame::getGame($this->id_game);
        $round = $game->getRound();
        $phase = $game->getIdPhase();
        if ($phase > PHASE_LANDMOVE) {
            ++$round;
        }

        $move = ModelLandMove::getLandMove($this->id_game, $id_move);
        $move_round = $move->getRound();
        if ($round != $move_round) {
            throw new ControllerException('Cannot validate move as it is not from the correct round.');
        }

        return $this->validateLandMove($id_move, $round, $move->getSteps(), $move->getUnits());
    }

    /**
     * checks if there is a possible route for this 2 countries for the user
     *
     * @param int $id_start (int id_zarea)
     * @param int $id_destination (int id_zarea)
     * @param int $type (TYPE_LAND, TYPE_SEA, TYPE_AIR)
     * @param int $speed (>= 1)
     * @throws NullPointerException
     * @throws ControllerException
     * @return bool
     */
    public function checkPossibleRoute($id_start, $id_destination, $type, $speed) {
        $id_start = (int)$id_start;
        $id_destination = (int)$id_destination;
        $type = (int)$type;
        $speed = (int)$speed;
        if ($speed < 1) {
            throw new ControllerException('Invalild speed.');
        }
        if ($type !== TYPE_LAND && $type !== TYPE_SEA && $type !== TYPE_AIR) {
            throw new ControllerException('Invalid area type.');
        }

        $steps = 0;
        $next = array($id_start);
        $visited = array();

        while (true) {
            $current = $next;
            $next = array();
            if ($steps > $speed) {
                return false;
            }
            if (empty($current)) {
                return false;
            }
            foreach ($current as $id_zarea) {
                if ($id_zarea === $id_destination) {
                    return true; // destination found
                }
                $visited[] = $id_zarea;
                if (!$this->isAreaPassable($id_zarea, $type)) {
                    continue;
                }
                $zArea = ModelGameArea::getGameArea($this->id_game, $id_zarea);
                $a2a = $zArea->getAdjecents();
                foreach ($a2a as $id_a2a_zarea) {
                    $id_a2a_zarea = (int)$id_a2a_zarea;
                    if (!in_array($id_a2a_zarea, $visited) && !in_array($id_a2a_zarea, $current) && !in_array($id_a2a_zarea, $next)) {
                        $next[] = $id_a2a_zarea;
                    }
                }
            }
            ++$steps;
        }

        return false;
    }

    /**
     * checks if this move is valid, throws exception if not valid
     *
     * @param int $id_move
     * @param int $round
     * @param $steps array(int step_nr => int id_zarea) -> step_nr counting from 1 to x
     * @param $units array(int id_unit => count)
     * @throws NullPointerException
     * @throws ControllerException
     * @return boolean
     */
    private function validateLandMove($id_move, $round, $steps, $units) {
        $attacks = array();

        /*
         * check if user owns the start country
         */
        $id_start_area = $steps[1];
        $zArea = ModelGameArea::getGameArea($this->id_game, $id_start_area);
        if ($zArea->getIdUser() !== $this->id_user) {
            throw new ControllerException('Start country isn\'t owned by this user.');
        }

        /*
         * check if start area is land area
         */
        if ($zArea->getIdType() !== TYPE_LAND) {
            throw new ControllerException('Start area not a country.');
        }

        /*
         * check if enough units are left in the country -> iterate over all moves (except this), substract outgoing
         * check if there are any incoming units
         * count number of attacks
         */
        $ingameLandUnits = ModelInGameLandUnit::getUnitsByIdZAreaUser($this->id_game, $id_start_area, $this->id_user); //array(int id_unit => ModelInGameLandUnit)
        $area_units = array();
        $units_incoming = 0;
        $landUnitsIterator = ModelLandUnit::iterator();
        while ($landUnitsIterator->hasNext()) {
            /* @var $landUnit ModelLandMove */
            $landUnit = $landUnitsIterator->next();
            $id_unit = (int)$landUnit->getId();
            if ((isset($ingameLandUnits[$id_unit]))) {
                /* @var $ingameLandUnit ModelInGameLandUnit */
                $ingameLandUnit = $ingameLandUnits[$id_unit];
                $area_units[$id_unit] = $ingameLandUnit->getCount();
            } else {
                $area_units[$id_unit] = 0;
            }
        }
        $landMovesIterator = ModelLandMove::iterator($this->id_user, $this->id_game, $round);
        while ($landMovesIterator->hasNext()) {
            /* @var $landMove ModelLandMove */
            $landMove = $landMovesIterator->next();
            if ($landMove->getId() === $id_move) {
                continue; // only subtract units from other moves
            }

            $move_steps = $landMove->getSteps();
            $zTargetArea = ModelGameArea::getGameArea($this->id_game, end($move_steps));
            $zStartArea = ModelGameArea::getGameArea($this->id_game, reset($move_steps));
            // check if this is an attack
            if ($zTargetArea->getIdUser() !== $this->id_user && (!in_array($zTargetArea->getId(), $attacks))) {
                $attacks[] = $zTargetArea->getId();
            }
            if ($zStartArea->getId() === $id_start_area) {
                $move_units = $landMove->getUnits();
                foreach ($move_units as $id_unit => $count) {
                    $area_units[$id_unit] -= $count;
                }
            } else if ($zTargetArea->getId() === $id_start_area) {
                $move_units = $landMove->getUnits();
                foreach ($move_units as $id_unit => $count) {
                    $units_incoming += $count;
                }
            }
        }
        $total_units_left = $units_incoming;
        foreach ($area_units as $id_unit => $count) {
            if (isset($units[$id_unit]) && $units[$id_unit] > $count) {
                throw new ControllerException('Invalid move, not enough units in area.');
            }
            $count_leaving = isset($units[$id_unit]) ? $units[$id_unit] : 0;
            $total_units_left += ($count - $count_leaving);
        }
        if ($total_units_left <= 0) {
            throw new ControllerException('Can\'t leave country empty.');
        }

        /*
         * check if target area is reachable -> 2 cases: type land or type aircraft movement
         */
        $id_target_area = (int)end($steps);
        $type = TYPE_AIR;
        $speed = 99999;
        foreach ($units as $id_unit => $count) {
            if ($count <= 0) {
                continue;
            }
            /* @var $landUnit ModelLandUnit */
            $landUnit = ModelLandUnit::getModelById((int)$id_unit);
            if ($landUnit->getIdType() < $type) {
                $type = $landUnit->getIdType();
            }
            if ($landUnit->getSpeed() < $speed) {
                $speed = $landUnit->getSpeed();
            }
        }
        if (!$this->checkPossibleRoute($id_start_area, $id_target_area, $type, $speed)) {
            throw new ControllerException('Unable to find route between the 2 countries.');
        }

        /*
         * check if target area is enemy area, only MAX_LAND_ATTACKS attacks per round
         */
        $zTargetArea = ModelGameArea::getGameArea($this->id_game, $id_target_area);
        if ($zTargetArea->getIdUser() !== $this->id_user) { // move is an attack
            if (!in_array($zTargetArea->getId(), $attacks)) {
                $attacks[] = $zTargetArea->getId();
            }
            if (count($attacks) > MAX_LAND_ATTACKS) {
                throw new ControllerException('Unable to start any more attacks! Only ' . MAX_LAND_ATTACKS . ' per round allowed.');
            }
        }

        /*
         * check if target area is land area
         */
        if ($zTargetArea->getIdType() != TYPE_LAND) {
            throw new ControllerException('Destination not a country.');
        }

        return true;
    }

    private function isAreaPassable($id_zarea, $move_type) {
        $zArea = ModelGameArea::getGameArea($this->id_game, $id_zarea);
        $id_owner = (int)$zArea->getIdUser();
        $area_type = (int)$zArea->getIdType();

        if ($id_owner === $this->id_user) {
            if ($move_type === TYPE_AIR || $move_type === TYPE_LAND) {
                return true;
            }
            if ($area_type === $move_type) {
                return true;
            }
        }
        if ($id_owner === NEUTRAL_COUNTRY) {
            if ($move_type === TYPE_AIR) {
                return true;
            }
            if ($area_type === TYPE_SEA && $move_type === TYPE_SEA) {
                return true;
            }
        }

        return false;
    }

}
