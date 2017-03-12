<?php
namespace Attack\Controller\Game\Moves;

use Attack\Controller\Interfaces\PhaseController;

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

    public function addCreateTradeRouteMove() {
        // TODO : implement
    }

    public function addDeleteTradeRouteMove() {
        // TODO : implement
    }

    public function checkShortestRoute($id_game_area_start, $id_game_area_destination) {
        // TODO : implement
        return 3;
    }

}