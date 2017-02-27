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

class ModelSeaMove extends ModelMove {

    /**
     * array(int id_game => array(int id_move => ModelSeaMove))
     *
     * @var array
     */
    private static $moves = [];

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
        // TODO : implement
        return null;
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
            $moves[] = self::getMoveByid((int)$id_game, (int)$move['id']);
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
     * @throws NullPointerException
     * @throws ModelException
     * @return ModelSeaMove
     */
    public static function create($id_user, $id_game, $round, $steps, $id_game_ship) {
        // TODO : implement
        return null;
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