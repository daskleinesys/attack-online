<?php
namespace AttOn\Controller;

use AttOn\Controller\Logic\Factories\Interfaces\LogicFactoryInterface;
use AttOn\Controller\Logic\Operations\Interfaces\PhaseLogic;
use AttOn\Exceptions\ControllerException;
use AttOn\Exceptions\LogicException;
use AttOn\Model\Game\ModelGame;
use AttOn\Tools\Autoloader;

class CronMain {

    private $logger;
    private $factories;
    private $errors = array(); // array(int id_game => string error_msg)

    public function __construct() {
        $this->logger = \Logger::getLogger('CronMain');

        // factory pattern
        global $env;
        $this->factories = Autoloader::loadFactories($env['basepath'] . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR . 'Logic' . DIRECTORY_SEPARATOR . 'Factories' . DIRECTORY_SEPARATOR, 'AttOn\\Controller\\Logic\\Factories\\');
    }

    /**
     * run cronjob
     *
     * @param [$id_game int]
     * @return void
     */
    public function execute($id_game = null) {
        if ($id_game !== null) {
            try {
                $this->run_game((int)$id_game);
            } catch (ControllerException $ex) {
                $this->logger->fatal($ex);
                $this->errors[$id_game] = $ex->getMessage();
            } catch (LogicException $ex) {
                $this->logger->fatal($ex);
                $this->errors[$id_game] = $ex->getMessage();
            }
        } else {
            $this->check_games();
        }

    }

    /**
     * have there been errors?
     *
     * @return boolean - true if errors occured
     */
    public function hasErrors() {
        return (!empty($this->errors));
    }

    /**
     * returns errors if any
     *
     * @return array(int id_game => string error_msg)
     */
    public function getErrors() {
        return $this->errors;
    }

    private function check_games() {
        $this->logger->debug('checking for applicable games');
        foreach (ModelGame::getGamesForProcessing() as $id_game) {
            try {
                $this->run_game((int)$id_game);
            } catch (ControllerException $ex) {
                $this->logger->fatal($ex);
                $this->errors[$id_game] = $ex->getMessage();
            } catch (LogicException $ex) {
                $this->logger->fatal($ex);
                $this->errors[$id_game] = $ex->getMessage();
            }
        }
    }

    private function run_game($id_game) {
        $this->logger->debug('run logic for game: ' . $id_game);
        $game = ModelGame::getGame($id_game);

        foreach ($this->factories as $factory) {
            /* @var $factory LogicFactoryInterface */
            if ($factory->getIdPhase() === $game->getIdPhase()) {
                $phaseLogic = $factory->getOperation($id_game);
            }
        }

        if (!isset($phaseLogic)) {
            throw new ControllerException('No operation loaded.');
        }

        /* @var $phaseLogic PhaseLogic */
        $phaseLogic->run();
    }

}
