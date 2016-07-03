<?php
namespace AttOn\Controller\Game\InGame;

use AttOn\Controller\Interfaces\PhaseController;
use AttOn\Exceptions\ControllerException;

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

        // TODO : check if all ships are set

        $this->fixatePhase(true);
    }

    public function createSetShipsMove() {

        // TODO : implement

        // 1. regex name --> done in controller
        // 2. check if id_zarea_in_port belongs to user --> done in controller
        // 3. check if zarea and id_zarea_in_port are adjacent --> done in controller
        // 4. check if id_unit is a ship --> done in controller
    }

}
