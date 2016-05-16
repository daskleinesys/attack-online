<?php
namespace AttOn\Controller\Game\InGame;

use AttOn\Controller\Interfaces\PhaseController;
use AttOn\Exceptions\ControllerException;
use AttOn\Model\Atton\InGame\ModelGameArea;
use AttOn\Model\Atton\InGame\Moves\ModelProductionMove;
use AttOn\Model\Game\ModelGame;

class ProductionController extends PhaseController {


    /**
     * @param int $id_user - id of the user accessing the moderation actions
     * @param int $id_game - id of currently selected game
     * @return ProductionController
     */
    public function __construct($id_user, $id_game) {
        parent::__construct((int)$id_user, (int)$id_game, PHASE_PRODUCTION);
    }

    /**
     * fixates the move if no error occured
     *
     * @return void
     */
    public function finishMove() {
        $this->fixatePhase(true);
    }

    public function createProductionMove($id_zarea, $units) {
        $id_zarea = (int)$id_zarea;

        // check if already fixated
        if ($this->checkIfDone()) {
            throw new ControllerException('ProductionMove already finished.');
        }

        // check if processing
        $game = ModelGame::getGame($this->id_game);
        if ($game->checkProcessing()) {
            throw new ControllerException('Unable to create moves at this moment as the game-logic is currently processing.');
        }
        // check if valid area picked
        if ($id_zarea === 0) {
            throw new ControllerException('Choose a start and destination country.');
        }
        ModelGameArea::getGameArea($this->id_game, $id_zarea);
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

        $round = (int)$game->getRound();
        $phase = (int)$game->getIdPhase();
        if ($phase > PHASE_PRODUCTION) {
            ++$round;
        }

        $this->validateNewProductionMove($round, $id_zarea, $units);
        return ModelProductionMove::createProductionMove($this->id_user, $this->id_game, $round, $id_zarea, $units);
    }

    public function deleteProductionMove($id_move) {
        $id_move = intval($id_move);

        // check if move exists
        $move = ModelProductionMove::getProductionMove($this->id_game, $id_move);
        // check if move is from user
        if ($this->id_user !== $move->getIdUser()) {
            throw new ControllerException('Unable to delete move from another user.');
        }
        // check if already fixated
        if ($this->checkIfDone()) {
            throw new ControllerException('ProductionMove already finished.');
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
        if ($phase > PHASE_PRODUCTION) {
            ++$round;
        }
        if ($round != $move_round) {
            throw new ControllerException('Unable to delete move as it is not from the correct round.');
        }

        // delete move
        $move->flagMoveDeleted();
        return;
    }

    private function validateNewProductionMove($round, $id_zarea, $units) {
        // TODO : implement
    }

}