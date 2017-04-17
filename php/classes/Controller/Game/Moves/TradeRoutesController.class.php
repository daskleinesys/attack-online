<?php
namespace Attack\Controller\Game\Moves;

use Attack\Controller\Interfaces\PhaseController;
use Attack\Exceptions\ControllerException;
use Attack\Exceptions\NullPointerException;
use Attack\Model\Game\ModelGame;
use Attack\Model\Game\ModelTradeRoute;
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

    /**
     * validate move according to current traderoute and other moves
     * if valid -> create new traderoute move
     *
     * @param int $id_user
     * @param int $id_game
     * @param int $round
     * @param array $steps
     * @throws ControllerException
     */
    public function create($id_user, $id_game, $round, array $steps) {
        $validator = new TradeRouteValidator($id_user, $id_game, $round);
        $validator->validateMove($steps, true);
        ModelTradeRouteMove::create($id_user, $id_game, $round, $steps);
    }

    /**
     * validate and create deletion move
     *
     * @param int $id_user
     * @param int $id_game
     * @param int $round
     * @param int $id_traderoute
     * @throws ControllerException
     */
    public function deleteTradeRoute($id_user, $id_game, $round, $id_traderoute) {
        // check if traderoute exists
        try {
            $traderoute = ModelTradeRoute::getById($id_traderoute);
        } catch (NullPointerException $ex) {
            throw new ControllerException('traderoute doesn\'t exist');
        }

        // check if it is owned by user and in correct game
        if ($traderoute->getIdUser() !== $id_user) {
            throw new ControllerException('traderoute doesn\'t belong to current user');
        }
        if ($traderoute->getIdGame() !== $id_game) {
            throw new ControllerException('traderoute doesn\'t belong to current game');
        }

        // check if already fixated
        if ($this->checkIfDone()) {
            throw new ControllerException('current phase already finished');
        }

        // check if processing
        $game = ModelGame::getGame($id_game);
        if ($game->checkProcessing()) {
            throw new ControllerException('game-logic is currently processing');
        }

        // check if deletion for move already exists
        $iterator = ModelTradeRouteMove::iterator($id_user, $id_game, $round);
        while ($iterator->hasNext()) {
            /** @var ModelTradeRouteMove $move */
            $move = $iterator->next();
            if (count($move->getSteps()) !== 2) {
                continue;
            }
            if ($move->getSteps()[0] === $traderoute->getSteps()[0]) {
                throw new ControllerException('deletion for move already exists');
            }
        }

        // create deletion
        $traderouteSteps = $traderoute->getSteps();
        $steps = [];
        $steps[] = current($traderouteSteps);
        $steps[] = end($traderouteSteps);
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
