<?php
namespace AttOn\Controller\Logic\Operations;

use AttOn\Controller\Game\InGame\LandMoveController;
use AttOn\Controller\Logic\Operations\Interfaces\PhaseLogic;
use AttOn\Exceptions\ControllerException;
use AttOn\Exceptions\LogicException;
use AttOn\Model\Atton\InGame\ModelGameArea;
use AttOn\Model\Atton\InGame\ModelInGameLandUnit;
use AttOn\Model\Atton\InGame\Moves\ModelLandMove;
use AttOn\Model\Atton\InGame\Moves\ModelProductionMove;
use AttOn\Model\Atton\ModelLandUnit;
use AttOn\Model\Game\Dice\DieSix;
use AttOn\Model\Game\ModelGame;

class LogicProduction extends PhaseLogic {
    private $logger;

    private $moves = array(); // array (ModelProductionMove $move)

    /**
     * returns object to run game logic -> should only be called by factory
     *
     * @param $id_game int
     * @return LogicLandMove
     */
    public function __construct($id_game) {
        parent::__construct($id_game, PHASE_PRODUCTION);
        $this->logger = \Logger::getLogger('LogicProduction');
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
             * 1. run through all moves and validate them
             */
            $this->validateMoves();

            /*
             * 2. execute production
             */
            foreach ($this->moves as $move) {
                $this->executeProduction($move);
            }

            // TODO : remove as soon as production is finished dev
            throw new \Exception('not finished');

            $this->finishProcessing();

        } catch (\Exception $ex) {
            $this->logger->fatal($ex);
            $this->rollback();
        }
    }

    private function validateMoves() {
        // TODO : get all moves and validate them
    }

    private function executeProduction(ModelProductionMove $move) {
        // TODO : execute move (add units and change money for user)
    }

}
