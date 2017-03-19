<?php
namespace Attack\Controller\Game\Moves;

use Attack\Controller\Interfaces\PhaseController;
use Attack\Exceptions\ControllerException;
use Attack\Exceptions\NullPointerException;
use Attack\Model\Game\ModelGame;
use Attack\Model\Game\Moves\ModelTradeRouteMove;

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
     * @throws ControllerException
     */
    public function finishMove() {
        $this->fixatePhase(true);
    }

    public function create($id_user, $id_game, $round, array $steps) {
        $validator = new TradeRouteValidator($id_user, $id_game, $round);
        $validator->validateMove($steps, true);
        ModelTradeRouteMove::create($id_user, $id_game, $round, $steps);
    }

    /**
     * checks if the user is allowed to delete this move
     * then deletes it from database
     *
     * @param $id_move
     * @throws ControllerException
     * @throws NullPointerException
     */
    public function deleteMove($id_move) {
        // check if move exists
        $move = ModelTradeRouteMove::getById($id_move);

        // check if move is from user
        if ($this->id_user !== $move->getIdUser()) {
            throw new ControllerException('Unable to delete move from another user.');
        }
        // check if already fixated
        if ($this->checkIfDone()) {
            throw new ControllerException('Current phase already finished.');
        }
        // check if processing
        $game = ModelGame::getGame($this->id_game);
        if ($game->checkProcessing()) {
            throw new ControllerException('Unable to delete moves at this moment as the game-logic is currently processing.');
        }
        // check if move is from the current round
        $move_round = $move->getRound();
        $round = $game->getRound();
        $phase = $game->getIdPhase();
        if ($phase > PHASE_TRADEROUTES) {
            ++$round;
        }
        if ($round != $move_round) {
            throw new ControllerException('Unable to delete move as it is not from the correct round.');
        }

        // delete move
        ModelTradeRouteMove::delete($move);

    }

}
