<?php
namespace Attack\GameLogic\Operations;

use Attack\Controller\Game\Moves\TroopsMoveController;
use Attack\GameLogic\Operations\Interfaces\PhaseLogic;
use Attack\Exceptions\ControllerException;
use Attack\Exceptions\LogicException;
use Attack\Model\Game\ModelGameLandUnit;
use Attack\Model\Game\Moves\ModelTroopsMove;
use Attack\Model\Game\ModelGame;

class LogicTroopsMove extends PhaseLogic {
    private $logger;

    private $troop_moves = array(); // array (int $id_move)

    /**
     * returns object to run game logic -> should only be called by factory
     *
     * @param $id_game int
     */
    public function __construct($id_game) {
        parent::__construct($id_game, PHASE_TROOPSMOVE);
        $this->logger = \Logger::getLogger('LogicTroopsMove');
    }

    /**
     * run the game logic
     *
     * @throws LogicException
     * @return void
     */
    public function run() {
        if (!$this->checkIfValid()) {
            throw new LogicException('Game ' . $this->id_game . ' not valid for processing.');
        }
        $this->startProcessing();

        try {
            /*
             * 1. run through all moves and validtae
             */
            $this->validateMoves();

            /*
             * 2. execute troop movements
             */
            foreach ($this->troop_moves as $id_move) {
                $this->executeTroopMovement($id_move);
            }

            $this->finishProcessing();

        } catch (\Exception $ex) {
            $this->logger->fatal($ex);
            $this->rollback();
        }
    }

    private function validateMoves() {
        $game = ModelGame::getGame($this->id_game);
        $round = $game->getRound();
        $move_iter = ModelTroopsMove::iterator(null, $this->id_game, $round);
        $controllerForUser = array();
        $controller = null;

        // run through moves
        while ($move_iter->hasNext()) {
            /* @var $move ModelTroopsMove */
            $move = $move_iter->next();
            $id_move = $move->getId();
            $id_user = $move->getIdUser();

            // validate moves
            if (!isset($controllerForUser[$id_user])) {
                $controllerForUser[$id_user] = new TroopsMoveController($id_user, $this->id_game);
            }
            try {
                $controller = $controllerForUser[$id_user];
                /* @var $controller TroopsMoveController */
                $controller->validateMoveByid($id_move);
            } catch (ControllerException $ex) {
                $this->logger->error($ex);
                $move->flagMoveDeleted();
                continue;
            }
            $this->troop_moves[] = $id_move;
        }
    }

    private function executeTroopMovement($id_move) {
        $move = ModelTroopsMove::getMove($this->id_game, $id_move);
        $id_user = $move->getIdUser();
        $steps = $move->getSteps();
        $from = reset($steps);
        $to = end($steps);
        $units = $move->getUnits();
        foreach ($units as $id_unit => $count) {
            $landUnit_from = ModelGameLandUnit::getModelByIdGameAreaUserUnit($this->id_game, $from, $id_user, $id_unit);
            $landUnit_from->addCount($count * -1);
            $landUnit_to = ModelGameLandUnit::getModelByIdGameAreaUserUnit($this->id_game, $to, $id_user, $id_unit);
            $landUnit_to->addCount($count);
        }
    }

}
