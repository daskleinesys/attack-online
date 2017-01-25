<?php
namespace Attack\Controller\Logic\Operations;

use Attack\Controller\Game\InGame\ProductionController;
use Attack\Controller\Logic\Operations\Interfaces\PhaseLogic;
use Attack\Exceptions\ControllerException;
use Attack\Exceptions\LogicException;
use Attack\Model\Atton\InGame\ModelInGameLandUnit;
use Attack\Model\Atton\InGame\Moves\ModelProductionMove;
use Attack\Model\Game\ModelGame;
use Attack\Model\User\ModelIsInGameInfo;
use Attack\Tools\UserViewHelper;

class LogicProduction extends PhaseLogic {
    private $logger;

    private $moves = array(); // array (ModelProductionMove $move)
    private $spent_production = array(); // array (int $id_user => int $spent_production_value)

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

            /*
             * 3. update money for each user
             */
            $this->updateUserMoney();

            $this->finishProcessing();

        } catch (\Exception $ex) {
            $this->logger->fatal($ex);
            $this->rollback();
        }
    }

    private function validateMoves() {
        $game = ModelGame::getGame($this->id_game);
        $round = $game->getRound();
        $move_iter = ModelProductionMove::iterator(null, $this->id_game, $round);
        $controllerForUser = array();
        $controller = null;

        // run through moves
        while ($move_iter->hasNext()) {
            /* @var $move ModelProductionMove */
            $move = $move_iter->next();
            $id_user = $move->getIdUser();

            // validate moves
            if (!isset($controllerForUser[$id_user])) {
                $controllerForUser[$id_user] = new ProductionController($id_user, $this->id_game);
            }
            try {
                $controller = $controllerForUser[$id_user];
                /* @var $controller ProductionController */
                $controller->validateProductionMove($move);
            } catch (ControllerException $ex) {
                $this->logger->error($ex);
                $move->flagMoveDeleted();
                continue;
            }

            // add move to moves
            $this->moves[] = $move;
        }
    }

    private function executeProduction(ModelProductionMove $move) {
        $id_user = $move->getIdUser();
        $id_zarea = $move->getIdZArea();
        foreach ($move->getUnits() as $id_unit => $count) {
            $inGameLandUnits = ModelInGameLandUnit::getModelByIdZAreaUserUnit($this->id_game, $id_zarea, $id_user, $id_unit);
            $inGameLandUnits->addCount($count);
        }

        if (!isset($this->spent_production[$id_user])) {
            $this->spent_production[$id_user] = 0;
        }
        $this->spent_production[$id_user] += $move->getCost();
    }

    private function updateUserMoney() {
        foreach ($this->spent_production as $id_user => $spent_production_value) {
            $current_production = UserViewHelper::getCurrentProductionForUserInGame($id_user, $this->id_game);
            $iig = ModelIsInGameInfo::getIsInGameInfo($id_user, $this->id_game);
            $iig->setMoney($current_production['sum'] - $spent_production_value);
        }
    }

}
