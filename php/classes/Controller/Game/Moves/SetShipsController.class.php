<?php
namespace Attack\Controller\Game\Moves;

use Attack\Controller\Interfaces\PhaseController;
use Attack\Exceptions\ControllerException;
use Attack\Exceptions\ModelException;
use Attack\Exceptions\NullPointerException;
use Attack\Model\Game\ModelGameArea;
use Attack\Model\Game\ModelGameShip;
use Attack\Model\Game\Moves\ModelSetShipsMove;
use Attack\Model\Game\Start\ModelStartShips;
use Attack\Model\Game\ModelGame;

class SetShipsController extends PhaseController {

    private $error = false;

    /**
     * @param int $id_user - id of the user accessing the moderation actions
     * @param int $id_game - id of currently selected game
     */
    public function __construct($id_user, $id_game) {
        parent::__construct($id_user, $id_game, PHASE_SETSHIPS);
    }

    /**
     * fixates the move if no error occured
     *
     * @throws ControllerException
     * @return void
     */
    public function finishMove() {
        if ($this->error) {
            return;
        }

        // check if all ships are set
        $stillAvailableShips = $this->getStillAvailableShips();
        foreach ($stillAvailableShips as $id => $numberof) {
            if ($numberof > 0) {
                throw new ControllerException('Set all ships first!');
            }
        }

        $this->fixatePhase(true);
    }

    /**
     * creates new ship and corresponding move
     *
     * @param $id_unit int
     * @param $name string
     * @param $id_game_area_in_port int
     * @param $id_game_area int
     * @throws ControllerException
     * @throws ModelException
     */
    public function setNewShip($id_unit, $name, $id_game_area_in_port, $id_game_area) {
        // 1. regex name
        $name = trim($name);
        if (!preg_match("/^([a-zA-Z0-9]+[a-zA-Z0-9' -]+[a-zA-Z0-9']+){1,}?$/", $name)) {
            throw new ControllerException('Invalid ship name. Only letters, numbers, spaces and -\' allowed');
        }

        // 2. check if game_area_in_port belongs to user
        $port_area = ModelGameArea::getGameArea($this->id_game, (int)$id_game_area_in_port);
        if ($port_area->getIdUser() !== $this->id_user) {
            throw new ControllerException('Area doesn\'t belong to user.');
        }

        // 3. check if game_area and game_area_in_port are adjacent
        $game_area = ModelGameArea::getGameArea($this->id_game, (int)$id_game_area);
        if (!in_array($game_area->getIdArea(), $port_area->getAdjecents())) {
            throw new ControllerException('Area not adjacent do port area.');
        }

        // 4. check if ship id is in still available ships
        $stillAvailableShips = $this->getStillAvailableShips();
        if (!isset($stillAvailableShips[$id_unit])) {
            throw new ControllerException('No ships of this type available.');
        } else if ($stillAvailableShips[$id_unit] <= 0) {
            throw new ControllerException('No ships of this type available anymore.');
        }

        // 5. create new move
        ModelSetShipsMove::createSetShipsMove($this->id_user, $this->id_game, (int)$id_game_area_in_port, (int)$id_game_area, (int)$id_unit, $name);
    }

    /**
     * delete move and corresponding ship from database
     *
     * @param $id_move
     * @throws ControllerException
     * @throws NullPointerException
     */
    public function deleteMove($id_move) {
        // 1. check if it is a setship move from the active player
        $move = ModelSetShipsMove::getSetShipsMove($this->id_game, $id_move);
        if ($move->getIdUser() !== $this->id_user) {
            throw new ControllerException('Unable to delete move from another player.');
        }
        // 2. delete
        ModelSetShipsMove::deleteSetShipsMove($move);
    }

    /**
     * get list of all ships that are still placebale
     *
     * @return array - array(int id_unit => int numberof)
     */
    public function getStillAvailableShips() {
        // 1. get list of all StartShips
        try {
            $startShips = ModelStartShips::getStartShipsForPlayers(ModelGame::getGame($this->id_game)->getNumberOfPlayers())->getShips();
        } catch (NullPointerException $ex) {
            return array();
        }

        // 2. get list of all StartShipMoves and reduce list of Startships
        $moves = ModelSetShipsMove::getSetShipMovesForUser($this->id_user, $this->id_game);
        while ($moves->hasNext()) {
            /** @var $move ModelSetShipsMove */
            $move = $moves->next();
            $ship = ModelGameShip::getShipById($this->id_game, $move->getIdGameUnit());
            $id_unit = $ship->getIdUnit();
            if (isset($startShips[$id_unit])) {
                --$startShips[$id_unit];
            }
        }

        // 3. return
        return $startShips;
    }

    /**
     * validate move and throw ControllerException if move is not valid
     *
     * @param ModelSetShipsMove $move
     * @throws ControllerException
     */
    public function validateSetShipsMove(ModelSetShipsMove $move) {
        // 1. check if id_game_area_in_port belongs to user
        $port_area = ModelGameArea::getGameArea($this->id_game, $move->getIdGameAreaInPort());
        if ($port_area->getIdUser() !== $move->getIdUser()) {
            throw new ControllerException('Area doesn\'t belong to user.');
        }

        // 2. check if zarea and id_game_area_in_port are adjacent
        $game_area = ModelGameArea::getGameArea($this->id_game, $move->getIdGameArea());
        if (!in_array($game_area->getIdArea(), $port_area->getAdjecents())) {
            throw new ControllerException('Area not adjacent do port area.');
        }

        // 3. check if ship id is in still available ships
        $stillAvailableShips = $this->getStillAvailableShips();
        $ship = ModelGameShip::getShipById($this->id_game, $move->getIdGameUnit());
        $id_unit = $ship->getIdUnit();
        if (!isset($stillAvailableShips[$id_unit])) {
            throw new ControllerException('No ships of this type available.');
        } else if ($stillAvailableShips[$id_unit] < 0) {
            throw new ControllerException('No ships of this type available anymore.');
        }
    }

}
