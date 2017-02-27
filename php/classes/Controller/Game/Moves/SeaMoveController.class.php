<?php
namespace Attack\Controller\Game\Moves;

use Attack\Controller\Interfaces\PhaseController;
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
     * check if user owns this move
     * check if move isnt history
     * check if phase not already fixated
     * check if game is currently processing (for this phase)
     * if valid -> move gets deleted from database
     *
     * @param ModelSeaMove $move
     * @return bool
     */
    public function deleteMove(ModelSeaMove $move) {
        // TODO : implement
        return true;
    }

    /**
     * check if ship has already move for current phase
     * check if target area is reachable
     * if valid -> create move and return it
     *
     * @param ModelGameShip $ship
     * @param ModelGameArea $targetArea
     * @param ModelGameArea|null $targetPortArea
     * @return ModelSeaMove
     */
    public function createMove(ModelGameShip $ship, ModelGameArea $targetArea, ModelGameArea $targetPortArea = null) {
        // TODO : implement
        return null;
    }

    /**
     * validate if given move is possible
     *
     * @param ModelSeaMove $move
     * @return bool
     */
    public function validateMove(ModelSeaMove $move) {
        // TODO : implement
        return true;
    }

}