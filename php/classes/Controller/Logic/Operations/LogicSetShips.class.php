<?php
namespace AttOn\Controller\Logic\Operations;

use AttOn\Controller\Logic\Operations\Interfaces\PhaseLogic;
use AttOn\Exceptions\LogicException;
use AttOn\Model\Atton\InGame\Moves\ModelSetShipsMove;

class LogicSetShips extends PhaseLogic {

    private $logger;

    /**
     * returns object to run game logic -> should only be called by factory
     * @param $id_game int
     * @return LogicSetShips
     */
    public function __construct($id_game) {
        parent::__construct($id_game, PHASE_SETSHIPS);
        $this->logger = \Logger::getLogger('LogicSetShips');
    }

    /**
     * run the game logic
     *
     * @throws LogicException
     * @return void
     */
    public function run() {
        if (!$this->checkIfValid()) {
            throw new LogicException('Game '.$this->id_game.' not valid for processing.');
        }
        $this->startProcessing();

        try {
            // run through moves for each user and validate
            $iter = ModelSetShipsMove::iterator($this->id_game);
            while ($iter->hasNext()) {
                // TODO : implement
                throw new \LogicException('implement!');
            }

            // add all ships
            // TODO : implement

            $this->finishProcessing();
        } catch (\Exception $ex) {
            $this->logger->fatal($ex);
            $this->rollback();
        }
    }

}
