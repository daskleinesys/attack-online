<?php
namespace Attack\Model\Atton\InGame\Moves;

use Attack\Model\Atton\InGame\ModelGameArea;
use Attack\Model\Atton\InGame\ModelInGameLandUnit;
use Attack\Model\Atton\InGame\Moves\Interfaces\ModelMove;
use Attack\Exceptions\ModelException;
use Attack\Exceptions\NullPointerException;
use Attack\Model\Atton\ModelLandUnit;
use Attack\Database\SQLConnector;
use Attack\Database\SQLCommands;
use Attack\Model\Iterator\ModelIterator;

class ModelTroopsMove extends ModelMove {

    private static $moves = array(); // array(int id_game => array(int id_move => ModelTroopsMove))

    private $steps = array(); // array(int step_nr => int id_zarea)
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
     * @param $steps array(int step_nr => int id_zarea)
     * @param $units array(int id_unit => count)
     * @return ModelTroopsMove
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
     * @return ModelTroopsMove
     */
    public static function getMove($id_game, $id_move) {
        if (isset(self::$moves[$id_game][$id_move])) {
            return self::$moves[$id_game][$id_move];
        }

        SQLCommands::init(intval($id_game));
        $query = 'get_land_move';
        $dict = array();
        $dict [':id_move'] = intval($id_move);
        $dict [':id_phase'] = PHASE_TROOPSMOVE;
        $result = SQLConnector::getInstance()->epp($query, $dict);
        if (empty($result)) {
            throw new NullPointerException('Move not found');
        }
        $steps = array();
        $units = array();
        foreach ($result as $line) {
            if ($line['step'] !== null && $line['id_zarea'] !== null) {
                $steps[$line['step']] = (int)$line['id_zarea'];
            }
            if ($line['id_unit'] !== null && $line['numberof'] !== null) {
                $units[$line['id_unit']] = (int)$line['numberof'];
            }
        }
        return self::$moves[$id_game][$id_move] = new ModelTroopsMove((int)$result[0]['id_user'], (int)$id_game, PHASE_TROOPSMOVE, (int)$id_move, (int)$result[0]['round'], (bool)$result[0]['deleted'], $steps, $units);
    }

    /**
     * returns an iterator for troopsmoves, specify round and/or user if necessary
     *
     * @param $id_user int
     * @param $id_game int
     * @param $round int
     * @return ModelIterator
     */
    public static function iterator($id_user = null, $id_game, $round = null) {
        SQLCommands::init(intval($id_game));
        $query = 'get_specific_moves';
        $dict = array();
        $dict[':id_phase'] = PHASE_TROOPSMOVE;
        $dict[':round'] = ($round === null) ? '%' : intval($round);
        if ($id_user === null) {
            $query = 'get_all_moves_for_phase_and_round';
        } else {
            $dict[':id_user'] = intval($id_user);
        }

        $result = SQLConnector::Singleton()->epp($query, $dict);
        $moves = array();
        foreach ($result as $move) {
            $moves[] = self::getMove((int)$id_game, (int)$move['id']);
        }

        return new ModelIterator($moves);
    }

    /**
     * creates troops move for user
     *
     * @param $id_user int
     * @param $id_game int
     * @param $round int
     * @param $steps array(int step_nr => int id_zarea) -> step_nr counting from 1 to x
     * @param $units array(int id_unit => count)
     * @throws NullPointerException
     * @throws ModelException
     * @return ModelTroopsMove
     */
    public static function createMove($id_user, $id_game, $round, $steps, $units) {
        SQLCommands::init(intval($id_game));

        // CREATE MOVE
        $query = 'create_move';
        $dict = array();
        $dict [':id_user'] = intval($id_user);
        $dict [':id_phase'] = PHASE_TROOPSMOVE;
        $dict [':round'] = $round;
        SQLConnector::Singleton()->epp($query, $dict);
        $id_move = SQLConnector::getInstance()->getLastInsertId();

        try {
            // INSERT MOVE STEPS
            $x = 0;
            foreach ($steps as $step => $id_zarea) {
                ++$x;
                ModelGameArea::getGameArea((int)$id_game, (int)$id_zarea);
                if (!isset($steps[$x])) {
                    throw new ModelException('Cannot create troops move, steps not consistent.');
                }
                $query = 'insert_area_for_move';
                $dict = array();
                $dict [':id_move'] = intval($id_move);
                $dict [':step'] = intval($step);
                $dict [':id_zarea'] = intval($id_zarea);
                SQLConnector::Singleton()->epp($query, $dict);
            }
            $id_zarea_start = (int)$steps[1];
            // INSERT UNITS
            foreach ($units as $id_unit => $count) {
                ModelLandUnit::getModelById($id_unit);
                $zUnit = ModelInGameLandUnit::getModelByIdZAreaUserUnit((int)$id_game, $id_zarea_start, (int)$id_user, (int)$id_unit);
                $query = 'insert_land_units_for_move';
                $dict = array();
                $dict [':id_zunit'] = $zUnit->getId();
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

        return self::$moves[$id_game][$id_move] = new ModelTroopsMove((int)$id_user, (int)$id_game, PHASE_TROOPSMOVE, (int)$id_move, (int)$round, false, $steps, $units);
    }

    /**
     * @return array(int step_nr => int id_zarea)
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
