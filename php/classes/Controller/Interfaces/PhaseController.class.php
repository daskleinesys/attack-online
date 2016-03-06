<?php
namespace AttOn\Controller\Interfaces;

use AttOn\Model\User\ModelInGamePhaseInfo;

abstract class PhaseController extends ConstrictedController {

    protected $id_user;
    protected $id_game;
    protected $id_phase;

    /**
     * @param int $id_user - id of the user accessing the moderation actions
     * @param int $id_game - id of currently selected game
     * @param $id_phase
     */
    public function __construct($id_user, $id_game, $id_phase) {
        $this->id_user = intval($id_user);
        $this->id_game = intval($id_game);
        $this->id_phase = intval($id_phase);
    }

    /**
     * fixates the move if no error occured
     *
     * @return void
     */
    public abstract function finishMove();

    /**
     * checks if user already finished this move, returns true if user is already finished
     *
     * @return boolean
     */
    protected function checkIfDone() {
        $igpi = ModelInGamePhaseInfo::getInGamePhaseInfo($this->id_user, $this->id_game);
        if ($igpi->getIsReadyForPhase($this->id_phase)) {
            return true;
        }
        return false;
    }

    /**
     * checks if user wants to get notified and sends email
     *
     * @return void
     */
    protected function notifyUser() {
        // TODO: check if user wants to get notified, if so --> send mail
    }

    /**
     * sets is ready to true for given phase
     *
     * @param $is_ready boolean
     * @return void
     */
    protected function fixatePhase($is_ready) {
        $igpi = ModelInGamePhaseInfo::getInGamePhaseInfo($this->id_user, $this->id_game);
        $igpi->setIsReady($this->id_phase, $is_ready);
    }

}
