<?php
namespace AttOn\Controller\Game\InGame;

use AttOn\Controller\Interfaces\PhaseController;
use AttOn\Exceptions\ControllerException;
use AttOn\Exceptions\ModelException;
use AttOn\Exceptions\NullPointerException;
use AttOn\Model\Atton\InGame\ModelGameArea;
use AttOn\Model\Atton\InGame\ModelInGameShip;
use AttOn\Model\Atton\InGame\Moves\ModelSetShipsMove;
use AttOn\Model\Atton\ModelStartShips;
use AttOn\Model\Game\ModelGame;

class SetShipsController extends PhaseController {

    private $error = false;

    /**
     * @param int $id_user - id of the user accessing the moderation actions
     * @param int $id_game - id of currently selected game
     * @return SetShipsController
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
        // TODO : implement
        throw new ControllerException('Set all ships first!');

        $this->fixatePhase(true);
    }

    /**
     * creates new ship and corresponding move
     *
     * @param $id_unit int
     * @param $name string
     * @param $zarea_in_port int
     * @param $zarea int
     * @throws ControllerException
     * @throws ModelException
     */
    public function setNewShip($id_unit, $name, $zarea_in_port, $zarea) {
        // 1. regex name
        $name = trim($name);
        if (!preg_match("/^([a-zA-Z0-9]+[a-zA-Z0-9' -]+[a-zA-Z0-9']+){3,}?$/", $name)) {
            throw new ControllerException('Invalid ship name. Only letters, numbers, spaces and -\' allowed');
        }

        // 2. check if id_zarea_in_port belongs to user
        $port_area = ModelGameArea::getGameArea($this->id_game, (int)$zarea_in_port);
        if ($port_area->getIdUser() !== $this->id_user) {
            throw new ControllerException('Area doesn\'t belong to user.');
        }

        // 3. check if zarea and id_zarea_in_port are adjacent
        if (!in_array((int)$zarea, $port_area->getAdjecents())) {
            throw new ControllerException('Area not adjacent do port area.');
        }

        // 4. check if ship id is in still available ships
        // TODO : implement

        // 5. create new move
        ModelSetShipsMove::createSetShipsMove($this->id_user, $this->id_game, (int)$zarea_in_port, (int)$zarea, (int)$id_unit, $name);
    }

    /**
     * delete move and corresponding ship from database
     *
     * @param $id_move
     */
    public function deleteMove($id_move) {
        // TODO : implement

        // 1. check if it is a setship move from the active player
        // 2. delete
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
            $ship = ModelInGameShip::getShipById($this->id_game, $move->getIdZunit());
            $id_unit = $ship->getIdUnit();
            if (isset($startShips[$id_unit])) {
                --$startShips[$id_unit];
            }
        }

        // 3. return
        return $startShips;
    }

}
