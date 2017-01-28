<?php
namespace Attack\Model\Game\Moves;

use Attack\Model\Game\ModelGameArea;
use Attack\Model\Game\ModelGameLandUnit;
use Attack\Model\Game\Moves\Interfaces\ModelMove;
use Attack\Exceptions\ModelException;
use Attack\Exceptions\NullPointerException;
use Attack\Model\Units\ModelLandUnit;
use Attack\Database\SQLConnector;
use Attack\Tools\Iterator\ModelIterator;

class ModelLandMove extends ModelMove {

    private static $moves = array(); // array(int id_game => array(int id_move => ModelLandMove))

    private $steps = array(); // array(int step_nr => int id_game_area)
    private $units = array(); // array(int id_unit => count)

    /**
     * creates the model
     *
     * @param $id_user int
     * @param $id_game int
     * @param $id_phase int
     * @param $id_move int
     * @param $round int
     * @param $deleted boolean
     * @param $steps array(int step_nr => int id_game_area)
     * @param $units array(int id_unit => count)
     */
    protected function __construct($id_user, $id_game, $id_phase, $id_move, $round, $deleted, $steps, $units) {
        parent::__construct($id_user, $id_game, $id_phase, $id_move, $round, $deleted);
        $this->steps = $steps;
        $this->units = $units;
    }

    /**
     * returns the corresponding model
     *
     * @param $id_game int
     * @param $id_move int
     * @throws NullPointerException
     * @return ModelLandMove
     */
    public static function getLandMove($id_game, $id_move) {
        if (isset(self::$moves[$id_game][$id_move])) {
            return self::$moves[$id_game][$id_move];
        }

        $query = 'get_land_move';
        $dict = array();
        $dict[':id_move'] = intval($id_move);
        $result = SQLConnector::getInstance()->epp($query, $dict);
        if (empty($result)) {
            throw new NullPointerException('Move not found');
        }
        $steps = array();
        $units = array();
        foreach ($result as $line) {
            if ($line['step'] !== null && $line['id_game_area'] !== null) {
                $steps[$line['step']] = (int)$line['id_game_area'];
            }
            if ($line['id_unit'] !== null && $line['numberof'] !== null) {
                $units[$line['id_unit']] = (int)$line['numberof'];
            }
        }
        return self::$moves[$id_game][$id_move] = new ModelLandMove((int)$result[0]['id_user'], (int)$id_game, PHASE_LANDMOVE, (int)$id_move, (int)$result[0]['round'], (bool)$result[0]['deleted'], $steps, $units);
    }

    /**
     * returns an iterator for landmoves, specify round and/or user if necessary
     *
     * @param $id_user int
     * @param $id_game int
     * @param $round int
     * @return ModelIterator
     */
    public static function iterator($id_user = null, $id_game, $round = null) {
        $query = 'get_game_moves_by_phase_round_user';
        $dict = array();
        $dict[':id_game'] = intval($id_game);
        $dict[':id_phase'] = PHASE_LANDMOVE;
        $dict[':round'] = ($round == null) ? '%' : intval($round);
        if ($id_user == null) {
            $query = 'get_game_moves_by_phase_round';
        } else {
            $dict[':id_user'] = intval($id_user);
        }

        $result = SQLConnector::Singleton()->epp($query, $dict);
        $moves = array();
        foreach ($result as $move) {
            $moves[] = self::getLandMove((int)$id_game, (int)$move['id']);
        }

        return new ModelIterator($moves);
    }

    /**
     * creates land move for user
     *
     * @param $id_user int
     * @param $id_game int
     * @param $round int
     * @param $steps array(int step_nr => int id_game_area) -> step_nr counting from 1 to x
     * @param $units array(int id_unit => count)
     * @throws NullPointerException
     * @throws ModelException
     * @return ModelLandMove
     */
    public static function createLandMove($id_user, $id_game, $round, $steps, $units) {
        // CREATE MOVE
        $query = 'insert_move';
        $dict = array();
        $dict [':id_game'] = intval($id_game);
        $dict [':id_user'] = intval($id_user);
        $dict [':id_phase'] = PHASE_LANDMOVE;
        $dict [':round'] = $round;
        SQLConnector::Singleton()->epp($query, $dict);
        $id_move = SQLConnector::getInstance()->getLastInsertId();

        try {
            // INSERT MOVE STEPS
            $x = 0;
            foreach ($steps as $step => $id_game_area) {
                ++$x;
                ModelGameArea::getGameArea((int)$id_game, (int)$id_game_area);
                if (!isset($steps[$x])) {
                    throw new ModelException('Cannot create landmove, steps not consistent.');
                }
                $query = 'insert_area_for_move';
                $dict = array();
                $dict [':id_move'] = intval($id_move);
                $dict [':step'] = intval($step);
                $dict [':id_game_area'] = intval($id_game_area);
                SQLConnector::Singleton()->epp($query, $dict);
            }
            $id_game_area_start = (int)$steps[1];
            // INSERT UNITS
            foreach ($units as $id_unit => $count) {
                ModelLandUnit::getModelById($id_unit);
                $gameUnit = ModelGameLandUnit::getModelByIdZAreaUserUnit((int)$id_game, $id_game_area_start, (int)$id_user, (int)$id_unit);
                $query = 'insert_land_units_for_move';
                $dict = array();
                $dict [':id_game_unit'] = $gameUnit->getId();
                $dict [':id_move'] = intval($id_move);
                $dict [':count'] = intval($count);
                SQLConnector::Singleton()->epp($query, $dict);
            }
        } catch (ModelException $ex) {
            self::flagMoveDeleted();
            throw $ex;
        } catch (NullPointerException $ex) {
            self::flagMoveDeleted();
            throw $ex;
        }

        return self::$moves[$id_game][$id_move] = new ModelLandMove((int)$id_user, (int)$id_game, PHASE_LANDMOVE, (int)$id_move, (int)$round, false, $steps, $units);
    }

    /**
     * @return array(int step_nr => int id_game_area)
     */
    public function getSteps() {
        return $this->steps;
    }

    /**
     * @return array(int id_unit => count)
     */
    public function getUnits() {
        return $this->units;
    }

}
