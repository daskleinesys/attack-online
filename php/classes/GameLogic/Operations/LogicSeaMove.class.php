<?php
namespace Attack\GameLogic\Operations;

use Attack\Controller\Game\Moves\SeaMoveController;
use Attack\Exceptions\ControllerException;
use Attack\GameLogic\Operations\Interfaces\PhaseLogic;
use Attack\Exceptions\LogicException;
use Attack\Model\Game\Dice\DieSix;
use Attack\Model\Game\ModelGame;
use Attack\Model\Game\ModelGameShip;
use Attack\Model\Game\Moves\ModelSeaMove;

class LogicSeaMove extends PhaseLogic {
    private $logger;

    private $die;

    private $shipMoves = [];

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
            // 1. validate and execute all moves
            $this->validateExecuteAllMoves();

            // 2. check for battles

            // 3. resolve battles

            if (true) {
                throw new \Exception('TODO : implement logic sea move!');
            }

            $this->finishProcessing();
        } catch (\Exception $ex) {
            $this->logger->fatal($ex);
            $this->rollback();
        }
    }

    private function validateExecuteAllMoves() {
        $iterator = ModelSeaMove::iterator(null, $this->id_game, ModelGame::getGame($this->id_game)->getRound());
        $controllerForUser = [];
        $controller = null;
        while ($iterator->hasNext()) {
            /** @var ModelSeaMove $move */
            $move = $iterator->next();
            $id_user = $move->getIdUser();
            $id_game_ship = $move->getIdGameShip();
            if (in_array($id_game_ship, $this->shipMoves)) {
                $this->logger->error('multiple moves for ship ' . $id_game_ship);
                $move->flagMoveDeleted();
                continue;
            }
            if (!isset($controllerForUser[$id_user])) {
                $controllerForUser[$id_user] = new SeaMoveController($id_user, $this->id_game);
            }
            try {
                $controller = $controllerForUser[$id_user];
                /* @var $controller SeaMoveController */
                $controller->validateMove($move);
            } catch (ControllerException $ex) {
                $this->logger->error($ex);
                $move->flagMoveDeleted();
                continue;
            }
            $steps = $move->getSteps();
            $ship = ModelGameShip::getShipById($this->id_game, $id_game_ship);
            $ship->setIdGameArea($steps[2][0]);
            $ship->setIdGameAreaInPort($steps[2][1]);
            $this->shipMoves[] = $id_game_ship;
        }
    }

}
