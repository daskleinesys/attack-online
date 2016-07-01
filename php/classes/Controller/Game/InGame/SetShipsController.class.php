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

}
