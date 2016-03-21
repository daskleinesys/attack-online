<?php
namespace AttOn\Controller\Logic\Operations;

use AttOn\Controller\Game\InGame\LandMoveController;
use AttOn\Controller\Logic\Operations\Interfaces\PhaseLogic;
use AttOn\Exceptions\ControllerException;
use AttOn\Exceptions\LogicException;
use AttOn\Model\Atton\InGame\ModelGameArea;
use AttOn\Model\Atton\InGame\ModelInGameLandUnit;
use AttOn\Model\Atton\InGame\Moves\ModelLandMove;
use AttOn\Model\Game\ModelGame;

class LogicLandMove extends PhaseLogic {
    private $logger;

    private $troop_moves = array(); // array (int $id_move)
    private $attacks = array(); // array[int $id_start_country][int $id_target_country] = array(int $id_move)
    private $finished_moves = array(); // array (int $id_move)
    private $checked_areas = array(); // array (int $id_zarea)

    /**
     * returns object to run game logic -> should only be called by factory
     *
     * @param $id_game int
     * @return LogicLandMove
     */
    public function __construct($id_game) {
        parent::__construct($id_game, PHASE_LANDMOVE);
        $this->logger = \Logger::getLogger('LogicLandMove');
    }

    /**
     * run the game logic
     *
     * @throws \Exception
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
             * 1. run through all moves
             * 1.a validate moves
             * 1.b sort moves into two groups: troop movements / attacks
             */
            $this->sortAndValidateMoves();

            /*
             * 2. execute troop movements
             */
            foreach ($this->troop_moves as $id_move) {
                $this->executeTroopMovement($id_move);
            }

            /*
             * 3. check for NML-fights
             * 3.a run through all attacks and check if there are mirror moves (for nml-fights)
             * 3.b execute nml-fights
             * 3.c create temporary moves for winner
             */
            foreach ($this->attacks as $id_move) {
                $this->checkForNMLFight($id_move);
            }

            /*
             * 4. execute remaining fights
             */
            foreach ($this->attacks as $id_move) {
                if (!in_array($id_move, $this->finished_moves)) {
                    $this->executeAttack($id_move);
                }
            }

            /*
             * 5. check for empty areas and revert to neutral if anyone found+
             * TODO : do not run through moves, check all countries if empty
             */
            foreach ($this->finished_moves as $id_move) {
                $this->checkForAbandonedArea($id_move);
            }

            // TODO : remove after dev and add finishProcessing
            throw new \Exception('rollback, still developing');
            //$this->finishProcessing();

        } catch (\Exception $ex) {
            $this->logger->fatal($ex);
            $this->rollback();
            throw $ex;
        }
    }

    private function sortAndValidateMoves() {
        $game = ModelGame::getGame($this->id_game);
        $round = $game->getRound();
        $move_iter = ModelLandMove::iterator(null, $this->id_game, $round);
        $controllerForUser = array();
        $controller = null;

        // run through moves
        while ($move_iter->hasNext()) {
            /* @var $move ModelLandMove */
            $move = $move_iter->next();
            $id_move = $move->getId();
            $id_user = $move->getIdUser();

            // validate moves
            if (!isset($controllerForUser[$id_user])) {
                $controllerForUser[$id_user] = new LandMoveController($id_user, $this->id_game);
            }
            try {
                $controller = $controllerForUser[$id_user];
                /* @var $controller LandMoveController */
                $controller->validateLandMoveByid($id_move);
            } catch (ControllerException $ex) {
                $this->logger->error($ex);
                $move->flagMoveDeleted();
                continue;
            }

            // sort moves
            $steps = $move->getSteps();
            $zArea = ModelGameArea::getGameArea($this->id_game, end($steps));
            if ($zArea->getIdUser() != $id_user) {
                if (!isset($this->attacks[reset($steps)])) {
                    $this->attacks[reset($steps)] = array();
                }
                if (!isset($this->attacks[reset($steps)][end($steps)])) {
                    $this->attacks[reset($steps)][end($steps)] = array();
                }
                $this->attacks[reset($steps)][end($steps)][] = $id_move;
            } else {
                $this->troop_moves[] = $id_move;
            }
        }
    }

    private function executeTroopMovement($id_move) {
        $move = ModelLandMove::getLandMove($this->id_game, $id_move);
        $id_user = $move->getIdUser();
        $steps = $move->getSteps();
        $from = reset($steps);
        $to = end($steps);
        $units = $move->getUnits();
        foreach ($units as $id_unit => $count) {
            $landUnit_from = ModelInGameLandUnit::getModelByIdZAreaUserUnit($this->id_game, $from, $id_user, $id_unit);
            $landUnit_from->addCount($count * -1);
            $landUnit_to = ModelInGameLandUnit::getModelByIdZAreaUserUnit($this->id_game, $to, $id_user, $id_unit);
            $landUnit_to->addCount($count);
        }
        $this->finished_moves[] = $id_move;
    }

    private function checkForNMLFight($id_move) {
        // TODO: code nml fights
    }

    private function executeAttack($id_move) {
        // TODO: code attacks
    }

    private function checkForAbandonedArea($id_move) {
        $move = ModelLandMove::getLandMove($this->id_game, $id_move);
        $steps = $move->getSteps();
        $from = reset($steps);
        if (in_array($from, $this->checked_areas)) {
            return;
        } else {
            $this->checked_areas[] = $from;
        }
        $zArea = ModelGameArea::getGameArea($this->id_game, $from);
        $id_user = $zArea->getIdUser();
        $units = ModelInGameLandUnit::getUnitsByIdZAreaUser($this->id_game, $from, $id_user);
        $count = 0;
        /* @var $landUnit ModelInGameLandUnit */
        foreach ($units as $landUnit) {
            $count += $landUnit->getCount();
        }
        if ($count > 0) {
            return;
        }

        // unit count <= 0 -> remove country to neutral
        $zArea = ModelGameArea::getGameArea($this->id_game, $from);
        $zArea->setIdUser(NEUTRAL_COUNTRY);
        // TODO : reset unit count
    }

}
