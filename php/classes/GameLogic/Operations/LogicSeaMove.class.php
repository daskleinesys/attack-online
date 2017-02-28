<?php
namespace Attack\GameLogic\Operations;

use Attack\GameLogic\Operations\Interfaces\PhaseLogic;
use Attack\Exceptions\LogicException;
use Attack\Model\Game\Dice\DieSix;

class LogicSeaMove extends PhaseLogic {
    private $logger;

    private $die;

    /**
     * returns object to run game logic -> should only be called by factory
     *
     * @param $id_game int
     */
    public function __construct($id_game) {
        parent::__construct($id_game, PHASE_SEAMOVE);
        $this->logger = \Logger::getLogger('LogicSeaMove');
        $this->die = new DieSix();
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
            if (true) {
                throw new \Exception('TODO : implement logic sea move!');
            }

            $this->finishProcessing();
        } catch (\Exception $ex) {
            $this->logger->fatal($ex);
            $this->rollback();
        }
    }

}
