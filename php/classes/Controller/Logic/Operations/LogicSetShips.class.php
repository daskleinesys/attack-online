<?php
namespace Attack\Controller\Logic\Operations;

use Attack\Controller\Game\InGame\SetShipsController;
use Attack\Controller\Logic\Operations\Interfaces\PhaseLogic;
use Attack\Exceptions\ControllerException;
use Attack\Exceptions\LogicException;
use Attack\Model\Atton\InGame\ModelInGameShip;
use Attack\Model\Atton\InGame\Moves\ModelSetShipsMove;

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
            $controllerForUser = array();
            $controller = null;

            // run through moves for each user and validate
            $iter = ModelSetShipsMove::iterator($this->id_game);
            while ($iter->hasNext()) {
                /* @var $move ModelSetShipsMove */
                $move = $iter->next();
                $id_user = $move->getIdUser();

                // validate moves
                if (!isset($controllerForUser[$id_user])) {
                    $controllerForUser[$id_user] = $controller = new SetShipsController($id_user, $this->id_game);
                }
                try {
                    $controller->validateSetShipsMove($move);
                } catch (ControllerException $ex) {
                    $this->logger->error($ex);
                    $move->flagMoveDeleted();
                    ModelInGameShip::deleteShip($this->id_game, $move->getIdZunit());
                    continue;
                }

                $ship = ModelInGameShip::getShipById($this->id_game, $move->getIdZunit());
                $ship->setIdZarea($move->getIdZarea());
                $ship->setIdZareaInPort($move->getIdZareaInPort());
            }

            $this->finishProcessing();
        } catch (\Exception $ex) {
            $this->logger->fatal($ex);
            $this->rollback();
        }
    }

}
