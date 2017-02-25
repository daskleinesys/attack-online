<?php
namespace Attack\Controller\Game\Moves;

use Attack\Controller\Interfaces\PhaseController;
use Attack\Exceptions\ControllerException;
use Attack\Exceptions\NullPointerException;
use Attack\Model\Game\ModelGameArea;
use Attack\Model\Game\ModelGameLandUnit;
use Attack\Model\Game\Moves\ModelTroopsMove;
use Attack\Model\Units\ModelLandUnit;
use Attack\Model\Game\ModelGame;

class TroopsMoveController extends PhaseController {

    /**
     * @param int $id_user - id of the user accessing the moderation actions
     * @param int $id_game - id of currently selected game
     */
    public function __construct($id_user, $id_game) {
        parent::__construct((int)$id_user, (int)$id_game, PHASE_TROOPSMOVE);
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
     * delete move for user
     *
     * @param int $id_move
     * @throws NullPointerException
     * @throws ControllerException
     * @return void
     */
    public function deleteMove($id_move) {
        $id_move = intval($id_move);

        // check if move exists
        $move = ModelTroopsMove::getMove($this->id_game, $id_move);
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
        if ($phase > PHASE_TROOPSMOVE) {
            ++$round;
        }
        if ($round !== $move_round) {
            throw new ControllerException('Unable to delete move as it is not from the correct round.');
        }

        // delete move
        $move->flagMoveDeleted();

        return;
    }

    /**
     * create land move for user
     *
     * @param int $start (id_game_area)
     * @param int $destination (id_game_area)
     * @param array $units (int $id_unit -> $int count)
     * @throws NullPointerException
     * @throws ControllerException
     * @return ModelTroopsMove
     */
    public function createMove($start, $destination, array $units) {
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
        if ($phase > PHASE_TROOPSMOVE) {
            ++$round;
        }

        $this->validateMove(0, $round, $steps, $units);
        return ModelTroopsMove::createMove($this->id_user, $this->id_game, $round, $steps, $units);
    }

    /**
     * checks if this move is valid, throws exception if not valid
     *
     * @param int $id_move
     * @throws NullPointerException
     * @throws ControllerException
     * @return boolean
     */
    public function validateMoveByid($id_move) {
        $id_move = intval($id_move);

        // check if move is not already over
        $game = ModelGame::getGame($this->id_game);
        $round = $game->getRound();
        $phase = $game->getIdPhase();
        if ($phase > PHASE_TROOPSMOVE) {
            ++$round;
        }

        $move = ModelTroopsMove::getMove($this->id_game, $id_move);
        $move_round = $move->getRound();
        if ($round != $move_round) {
            throw new ControllerException('Cannot validate move as it is not from the correct round.');
        }

        return $this->validateMove($id_move, $round, $move->getSteps(), $move->getUnits());
    }

    /**
     * checks if there is a possible route for this 2 countries for the user
     *
     * @param int $id_start (int id_game_area)
     * @param int $id_destination (int id_game_area)
     * @param int $type (TYPE_LAND, TYPE_SEA, TYPE_AIR)
     * @throws NullPointerException
     * @throws ControllerException
     * @return bool
     */
    public function checkPossibleRoute($id_start, $id_destination, $type) {
        $id_start = (int)$id_start;
        $id_destination = (int)$id_destination;
        $type = (int)$type;
        if ($type !== TYPE_LAND && $type !== TYPE_SEA && $type !== TYPE_AIR) {
            throw new ControllerException('Invalid area type.');
        }

        $steps = 0;
        $next = array($id_start);
        $visited = array();

        while (true) {
            $current = $next;
            $next = array();
            if (empty($current)) {
                return false;
            }
            foreach ($current as $id_game_area) {
                if ($id_game_area === $id_destination) {
                    return true; // destination found
                }
                $visited[] = $id_game_area;
                if (!$this->isAreaPassable($id_game_area, $type)) {
                    continue;
                }
                $gameArea = ModelGameArea::getGameArea($this->id_game, $id_game_area);
                $adjacentGameAreas = $gameArea->getAdjacentGameAreas();
                foreach ($adjacentGameAreas as $id_adjacent_game_area) {
                    $id_adjacent_game_area = (int)$id_adjacent_game_area;
                    if (!in_array($id_adjacent_game_area, $visited) && !in_array($id_adjacent_game_area, $current) && !in_array($id_adjacent_game_area, $next)) {
                        $next[] = $id_adjacent_game_area;
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
     * @param $steps array(int step_nr => int id_game_area) -> step_nr counting from 1 to x
     * @param $units array(int id_unit => count)
     * @throws NullPointerException
     * @throws ControllerException
     * @return boolean
     */
    private function validateMove($id_move, $round, $steps, $units) {
        /*
         * check if user owns the start country and if start area is land area
         */
        $id_start_area = $steps[1];
        $gameArea = ModelGameArea::getGameArea($this->id_game, $id_start_area);
        if ($gameArea->getIdUser() !== $this->id_user) {
            throw new ControllerException('Start country isn\'t owned by this user.');
        }

        /*
         * check if user owns destination area
         */
        if (ModelGameArea::getGameArea($this->id_game, end($steps))->getIdUser() !== $this->id_user) {
            throw new ControllerException('destination country isn\'t owned by this user.');
        }

        /*
         * check if enough units are left in the country -> iterate over all moves (except this), substract outgoing
         * check if there are any incoming units
         * count number of attacks
         */
        $ingameLandUnits = ModelGameLandUnit::getUnitsByIdGameAreaUser($this->id_game, $id_start_area, $this->id_user); //array(int id_unit => ModelGameLandUnit)
        $area_units = array();
        $units_incoming = false;
        $units_left = false;
        $landUnitsIterator = ModelLandUnit::iterator();
        while ($landUnitsIterator->hasNext()) {
            /* @var $landUnit ModelTroopsMove */
            $landUnit = $landUnitsIterator->next();
            $id_unit = (int)$landUnit->getId();
            if ((isset($ingameLandUnits[$id_unit]))) {
                /* @var $ingameLandUnit ModelGameLandUnit */
                $ingameLandUnit = $ingameLandUnits[$id_unit];
                $area_units[$id_unit] = $ingameLandUnit->getCount();
            } else {
                $area_units[$id_unit] = 0;
            }
        }
        $movesIterator = ModelTroopsMove::iterator($this->id_user, $this->id_game, $round);
        while ($movesIterator->hasNext()) {
            /* @var $troopsMove ModelTroopsMove */
            $troopsMove = $movesIterator->next();
            if ($troopsMove->getId() === $id_move) {
                continue; // only subtract units from other moves
            }

            $move_steps = $troopsMove->getSteps();
            $targetGameArea = ModelGameArea::getGameArea($this->id_game, end($move_steps));
            $startGameArea = ModelGameArea::getGameArea($this->id_game, reset($move_steps));
            if ($startGameArea->getId() === $id_start_area) {
                $move_units = $troopsMove->getUnits();
                foreach ($move_units as $id_unit => $count) {
                    $area_units[$id_unit] -= $count;
                }
            } else if ($targetGameArea->getId() === $id_start_area) {
                $move_units = $troopsMove->getUnits();
                foreach ($move_units as $id_unit => $count) {
                    if ($count > 0) {
                        $units_incoming = true;
                    }
                }
            }
        }
        foreach ($area_units as $id_unit => $count) {
            if (isset($units[$id_unit]) && $units[$id_unit] > $count) {
                throw new ControllerException('Invalid move, not enough units in area.');
            }
            $count_leaving = isset($units[$id_unit]) ? $units[$id_unit] : 0;
            if ($count - $count_leaving > 0) {
                $units_left = true;
            }
        }
        if (!$units_incoming && !$units_left) {
            throw new ControllerException('Can\'t leave country empty.');
        }

        /*
         * check if target area is reachable -> 2 cases: type land or type aircraft movement
         */
        $id_target_area = (int)end($steps);
        $type = TYPE_AIR;
        foreach ($units as $id_unit => $count) {
            if ($count <= 0) {
                continue;
            }
            /* @var $landUnit ModelLandUnit */
            $landUnit = ModelLandUnit::getModelById((int)$id_unit);
            if ($landUnit->getIdType() < $type) {
                $type = $landUnit->getIdType();
            }
        }
        if (!$this->checkPossibleRoute($id_start_area, $id_target_area, $type)) {
            throw new ControllerException('Unable to find route between the 2 countries.');
        }

        return true;
    }

    private function isAreaPassable($id_game_area, $move_type) {
        $gameArea = ModelGameArea::getGameArea($this->id_game, $id_game_area);
        $id_owner = (int)$gameArea->getIdUser();
        $area_type = (int)$gameArea->getIdType();

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
