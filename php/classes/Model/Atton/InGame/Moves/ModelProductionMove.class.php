<?php
namespace Attack\Model\Atton\InGame\Moves;

use Attack\Exceptions\ModelException;
use Attack\Exceptions\NullPointerException;
use Attack\Model\Atton\InGame\ModelGameArea;
use Attack\Model\Atton\InGame\ModelInGameLandUnit;
use Attack\Model\Atton\InGame\Moves\Interfaces\ModelMove;
use Attack\Model\Atton\ModelLandUnit;
use Attack\Database\SQLConnector;
use Attack\Database\SQLCommands;
use Attack\Model\Iterator\ModelIterator;

class ModelProductionMove extends ModelMove {

    private static $moves = array(); // array(int id_game => array(int id_move => ModelProductionMove))

    private $id_zarea; // int id_zarea
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
     * @param $id_zarea int id_zarea
     * @param $units array(int id_unit => count)
     * @return ModelProductionMove
     */
    protected function __construct($id_user, $id_game, $id_phase, $id_move, $round, $deleted, $id_zarea, $units) {
        parent::__construct($id_user, $id_game, $id_phase, $id_move, $round, $deleted);
        $this->id_zarea = $id_zarea;
        $this->units = $units;
    }

    /**
     * returns the corresponding model
     *
     * @param $id_game int
     * @param $id_move int
     * @throws NullPointerException
     * @return ModelProductionMove
     */
    public static function getProductionMove($id_game, $id_move) {
        if (isset(self::$moves[$id_game][$id_move])) {
            return self::$moves[$id_game][$id_move];
        }

        SQLCommands::init(intval($id_game));
        $query = 'get_production_move';
        $dict = array();
        $dict [':id_move'] = intval($id_move);
        $dict [':id_phase'] = PHASE_PRODUCTION;
        $result = SQLConnector::getInstance()->epp($query, $dict);
        if (empty($result)) {
            throw new NullPointerException('Move not found');
        }
        $id_zarea = 0;
        $units = array();
        foreach ($result as $line) {
            if ($line['step'] !== null && $line['id_zarea'] !== null) {
                $id_zarea = (int)$line['id_zarea'];
            }
            if ($line['id_unit'] !== null && $line['numberof'] !== null) {
                $units[$line['id_unit']] = (int)$line['numberof'];
            }
        }
        return self::$moves[$id_game][$id_move] = new ModelProductionMove((int)$result[0]['id_user'], (int)$id_game, PHASE_PRODUCTION, (int)$id_move, (int)$result[0]['round'], (bool)$result[0]['deleted'], $id_zarea, $units);
    }

    /**
     * returns an iterator for productionmoves, specify round and/or user if necessary
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
        $dict[':id_phase'] = PHASE_PRODUCTION;
        $dict[':round'] = ($round === null) ? '%' : intval($round);
        if ($id_user === null) {
            $query = 'get_all_moves_for_phase_and_round';
        } else {
            $dict[':id_user'] = intval($id_user);
        }

        $result = SQLConnector::Singleton()->epp($query, $dict);
        $moves = array();
        foreach ($result as $move) {
            $moves[] = self::getProductionMove((int)$id_game, (int)$move['id']);
        }

        return new ModelIterator($moves);
    }

    /**
     * creates land move for user
     *
     * @param $id_user int
     * @param $id_game int
     * @param $round int
     * @param $id_zarea int id_zarea
     * @param $units array(int id_unit => count)
     * @throws NullPointerException
     * @throws ModelException
     * @return ModelProductionMove
     */
    public static function createProductionMove($id_user, $id_game, $round, $id_zarea, $units) {
        SQLCommands::init(intval($id_game));

        // CREATE MOVE
        $query = 'create_move';
        $dict = array();
        $dict [':id_user'] = intval($id_user);
        $dict [':id_phase'] = PHASE_PRODUCTION;
        $dict [':round'] = $round;
        SQLConnector::Singleton()->epp($query, $dict);
        $id_move = SQLConnector::getInstance()->getLastInsertId();

        try {
            // INSERT MOVE STEPS
            ModelGameArea::getGameArea((int)$id_game, (int)$id_zarea);
            $query = 'insert_area_for_move';
            $dict = array();
            $dict [':id_move'] = intval($id_move);
            $dict [':step'] = intval(1);
            $dict [':id_zarea'] = intval($id_zarea);
            SQLConnector::Singleton()->epp($query, $dict);

            // INSERT UNITS
            foreach ($units as $id_unit => $count) {
                ModelLandUnit::getModelById($id_unit);
                $zUnit = ModelInGameLandUnit::getModelByIdZAreaUserUnit((int)$id_game, $id_zarea, (int)$id_user, (int)$id_unit);
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

        return self::$moves[$id_game][$id_move] = new ModelProductionMove((int)$id_user, (int)$id_game, PHASE_PRODUCTION, (int)$id_move, (int)$round, false, $id_zarea, $units);
    }

    /**
     * @return int id_zarea
     */
    public function getIdZArea() {
        return $this->id_zarea;
    }

    /**
     * @return array(int id_unit => count)
     */
    public function getUnits() {
        return $this->units;
    }

    /**
     * @throws NullPointerException
     * @return int total cost of this production move (all units)
     */
    public function getCost() {
        $costsSum = 0;
        $unit_iter = ModelLandUnit::iterator();
        while ($unit_iter->hasNext()) {
            /* @var $unit ModelLandUnit */
            $unit = $unit_iter->next();
            $id_unit = (int)$unit->getId();
            if (!isset($this->units[$id_unit]) || $this->units[$id_unit] <= 0) {
                continue;
            }
            $costsSum += (int)$unit->getPrice() * $this->units[$id_unit];
        }
        return $costsSum;
    }

}
