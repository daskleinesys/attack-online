<?php
namespace Attack\Model\Game\Moves;

use Attack\Model\Game\Moves\Interfaces\ModelMove;
use Attack\Exceptions\ModelException;
use Attack\Exceptions\NullPointerException;
use Attack\Database\SQLConnector;
use Attack\Tools\Iterator\ModelIterator;

class ModelSeaMove extends ModelMove {

    /**
     * array(int id_game => array(int id_move => ModelSeaMove))
     *
     * @var array
     */
    private static $moves = [];

    /**
     * array(
     *     int id_game => array(
     *         int round => array(
     *             int id_game_ship => ModelSeaMove
     *         )
     *     )
     * )
     *
     * @var array
     */
    private static $movesByShip = [];

    /**
     * references ModelGameArea
     * array(
     *     1 => array(int id_start_area[, id_start_port_area]),
     *     2 => array(int id_target_area[, id_target_port_area])
     * )
     *
     * @var array
     */
    private $steps = [];

    /**
     * references ModelGameShip
     *
     * @var int
     */
    private $id_game_ship;

    /**
     * creates the model
     *
     * database schema:
     * one move for each ship in each seamove phase per round
     * one entry in table game_move_has_units per ship/move
     * two to four entries in game_move_has_areas -> step=1 for start-area (and possibly start-port) and step=2 for target-area (and possibly target-port)
     *
     * @param $id_user int
     * @param $id_game int
     * @param $id_phase int
     * @param $id_move int
     * @param $round int
     * @param $deleted boolean
     * @param $steps array(1 => array(int id_start_area[, id_start_port_area]), 2 => array(int id_target_area[, id_target_port_area]))
     * @param $id_game_ship
     */
    protected function __construct($id_user, $id_game, $id_phase, $id_move, $round, $deleted, array $steps, $id_game_ship) {
        parent::__construct($id_user, $id_game, $id_phase, $id_move, $round, $deleted);
        $this->steps = $steps;
        $this->id_game_ship = $id_game_ship;
    }

    /**
     * returns the corresponding model
     *
     * @param $id_game int
     * @param $id_move int
     * @throws NullPointerException
     * @return ModelSeaMove
     */
    public static function getByid($id_game, $id_move) {
        if (isset(self::$moves[$id_game][$id_move])) {
            return self::$moves[$id_game][$id_move];
        }
        throw new NullPointerException('TODO : implement');
        // TODO : implement
    }

    /**
     * returns the corresponding model
     *
     * @param $id_game int
     * @param $round int
     * @param $id_game_ship int
     * @throws NullPointerException
     * @return ModelSeaMove
     */
    public static function getByShipId($id_game, $round, $id_game_ship) {
        if (isset(self::$movesByShip[$id_game][$round][$id_game_ship])) {
            return self::$movesByShip[$id_game][$round][$id_game_ship];
        }
        throw new NullPointerException('TODO : implement');
        // TODO : implement
    }

    /**
     * returns an iterator for seamoves, specify round and/or user if necessary
     *
     * @param $id_user int
     * @param $id_game int
     * @param $round int
     * @return ModelIterator
     */
    public static function iterator($id_user = null, $id_game, $round = null) {
        $query = 'get_game_moves_by_phase_round_user';
        $dict = array();
        $dict[':id_game'] = (int)$id_game;
        $dict[':id_phase'] = PHASE_SEAMOVE;
        $dict[':round'] = ($round == null) ? '%' : (int)$round;
        if ($id_user == null) {
            $query = 'get_game_moves_by_phase_round';
        } else {
            $dict[':id_user'] = (int)$id_user;
        }

        $result = SQLConnector::Singleton()->epp($query, $dict);
        $moves = array();
        foreach ($result as $move) {
            $moves[] = self::getByid((int)$id_game, (int)$move['id']);
        }

        return new ModelIterator($moves);
    }

    /**
     * creates sea move for user
     *
     * @param $id_user int
     * @param $id_game int
     * @param $round int
     * @param $steps array
     * @param $id_game_ship int
     * @return ModelSeaMove
     * @throws \Exception
     */
    public static function create($id_user, $id_game, $round, $steps, $id_game_ship) {
        SQLConnector::Singleton()->beginTransaction();

        try {
            // CREATE MOVE
            $query = 'insert_move';
            $dict = array();
            $dict[':id_game'] = $id_game;
            $dict[':id_user'] = $id_user;
            $dict[':id_phase'] = PHASE_SEAMOVE;
            $dict[':round'] = $round;
            SQLConnector::Singleton()->epp($query, $dict);
            $id_move = (int)SQLConnector::getInstance()->getLastInsertId();

            // INSERT MOVE STEPS
            $query = 'insert_area_for_move';
            $dict = array();
            $dict[':id_move'] = $id_move;
            $dict[':step'] = 1;
            $dict[':id_game_area'] = $steps[1][0];
            SQLConnector::Singleton()->epp($query, $dict);
            if ($steps[1][1] !== NO_AREA) {
                $dict[':id_game_area'] = $steps[1][1];
                SQLConnector::Singleton()->epp($query, $dict);
            }
            $dict[':step'] = 2;
            $dict[':id_game_area'] = $steps[2][0];
            SQLConnector::Singleton()->epp($query, $dict);
            if ($steps[2][1] !== NO_AREA) {
                $dict[':id_game_area'] = $steps[2][1];
                SQLConnector::Singleton()->epp($query, $dict);
            }

            // INSERT UNITS
            $query = 'insert_ship_for_move';
            $dict = array();
            $dict[':id_game_unit'] = $id_game_ship;
            $dict[':id_move'] = $id_move;
            SQLConnector::Singleton()->epp($query, $dict);

            // COMMIT ALL QUERIES
            SQLConnector::Singleton()->commit();
        } catch (\Exception $ex) {
            SQLConnector::Singleton()->rollBack();
            throw $ex;
        }

        $move = new ModelSeaMove($id_user, $id_game, PHASE_SEAMOVE, $id_move, $round, false, $steps, $id_game_ship);
        self::$movesByShip[$id_game][$round][$id_game_ship] = $move;
        self::$moves[$id_game][$id_move] = $move;
        return $move;
    }

    /**
     * deletes move from database
     *
     * @param ModelSeaMove $move
     * @return bool
     */
    public static function delete(ModelSeaMove $move) {
        // TODO : implement
        return true;
    }

    /**
     * @return array
     */
    public function getSteps() {
        return $this->steps;
    }

    /**
     * @return int
     */
    public function getGameShip() {
        return $this->id_game_ship;
    }

}