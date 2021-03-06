<?php
namespace Attack\Model\Game\Moves;

use Attack\Exceptions\DatabaseException;
use Attack\Exceptions\ModelException;
use Attack\Model\Game\ModelGameShip;
use Attack\Model\Game\Moves\Interfaces\ModelMove;
use Attack\Exceptions\NullPointerException;
use Attack\Database\SQLConnector;
use Attack\Tools\Iterator\ModelIterator;
use Attack\Model\User\ModelUser;

class ModelSetShipsMove extends ModelMove {

    private static $moves = array(); // array(int id_game => array(int id_move => ModelSetShipsMove))

    private $id_game_area_in_port;
    private $id_game_area;
    private $id_game_unit;

    /**
     * creates the model
     *
     * @param $id_game int
     * @param $id_move int
     * @param $id_user int
     * @param $deleted boolean
     * @param $id_game_area_in_port int
     * @param $id_game_area int
     * @param $id_game_unit int
     */
    protected function __construct($id_user, $id_game, $id_move, $deleted, $id_game_area_in_port, $id_game_area, $id_game_unit) {
        parent::__construct($id_user, $id_game, PHASE_SETSHIPS, $id_move, 0, $deleted);
        $this->id_game_area_in_port = (int)$id_game_area_in_port;
        $this->id_game_area = (int)$id_game_area;
        $this->id_game_unit = (int)$id_game_unit;
    }

    /**
     * returns the corresponding model
     *
     * @param $id_game int
     * @param $id_move int
     * @return ModelSetShipsMove
     * @throws NullPointerException
     */
    public static function getSetShipsMove($id_game, $id_move) {
        if (isset(self::$moves[$id_game][$id_move])) {
            return self::$moves[$id_game][$id_move];
        }

        $query = 'get_set_ships_move';
        $dict = array();
        $dict[':id_move'] = intval($id_move);
        $result = SQLConnector::getInstance()->epp($query, $dict);
        if (empty($result)) {
            throw new NullPointerException('Move not found');
        }
        $line = array_pop($result);
        return self::$moves[$id_game][$id_move] = new ModelSetShipsMove(
            (int)$line['id_user'],
            (int)$id_game,
            (int)$id_move,
            (bool)$line['deleted'],
            (int)$line['id_port_area'],
            (int)$line['id_game_area'],
            (int)$line['id_game_unit']
        );
    }

    /**
     * returns the corresponding model -> creates it if necessary
     *
     * @param $id_game int
     * @param $id_user int
     * @return ModelIterator
     * @throws NullPointerException
     */
    public static function getSetShipMovesForUser($id_user, $id_game) {
        $query = 'get_game_moves_by_phase_round_user';
        $dict = array();
        $dict[':id_game'] = intval($id_game);
        $dict[':id_user'] = intval($id_user);
        $dict[':id_phase'] = PHASE_SETSHIPS;
        $dict[':round'] = 0;
        $result = SQLConnector::getInstance()->epp($query, $dict);
        ModelUser::getUser($id_user);
        if (empty($result)) {
            return new ModelIterator(array());
        }
        $moves = array();
        foreach ($result as $move) {
            $moves[] = self::getSetShipsMove($id_game, (int)$move['id']);
        }
        return new ModelIterator($moves);
    }

    /**
     * returns an iterator for all select-start moves in this game
     *
     * @param $id_game int
     * @return ModelIterator
     */
    public static function iterator($id_game) {
        $query = 'get_game_moves_by_phase_round';
        $dict = array();
        $dict[':id_game'] = intval($id_game);
        $dict[':id_phase'] = PHASE_SETSHIPS;
        $dict[':round'] = 0;

        $result = SQLConnector::Singleton()->epp($query, $dict);
        $moves = array();
        foreach ($result as $move) {
            $id_move = $move['id'];
            $moves[] = self::getSetShipsMove($id_game, $id_move);
        }

        return new ModelIterator($moves);
    }

    /**
     * @param $id_user int
     * @param $id_game int
     * @param $id_game_area_in_port int
     * @param $id_game_area int
     * @param $id_unit int
     * @param $name string
     * @return ModelSetShipsMove
     * @throws ModelException
     * @throws DatabaseException
     */
    public static function createSetShipsMove($id_user, $id_game, $id_game_area_in_port, $id_game_area, $id_unit, $name) {
        $id_user = (int)$id_user;
        $id_game = (int)$id_game;
        $id_game_area_in_port = (int)$id_game_area_in_port;
        $id_game_area = (int)$id_game_area;
        $id_unit = (int)$id_unit;

        // 1. check if name is available
        $query = 'get_game_ship_by_name';
        $dict = array();
        $dict[':id_game'] = $id_game;
        $dict[':name'] = $name;
        $result = SQLConnector::Singleton()->epp($query, $dict);
        if (!empty($result)) {
            throw new ModelException('Name already taken.');
        }

        // 2. create ship
        $ship = ModelGameShip::createShip($id_user, $id_game, $id_unit, NO_AREA, $name, NO_AREA);
        $id_game_unit = $ship->getId();

        // 3. create new move
        $query = 'insert_move';
        $dict = array();
        $dict[':id_game'] = intval($id_game);
        $dict[':id_user'] = $id_user;
        $dict[':id_phase'] = PHASE_SETSHIPS;
        $dict[':round'] = 0;
        SQLConnector::Singleton()->epp($query, $dict);
        $id_move = (int)SQLConnector::getInstance()->getLastInsertId();

        // 4. set areas
        $query = 'insert_area_for_move';
        $dict = array();
        $dict[':id_move'] = $id_move;
        $dict[':step'] = 0;
        $dict[':id_game_area'] = $id_game_area_in_port;
        SQLConnector::Singleton()->epp($query, $dict);
        $dict[':step'] = 1;
        $dict[':id_game_area'] = $id_game_area;
        SQLConnector::Singleton()->epp($query, $dict);

        // 5. add new ship to move
        $query = 'insert_ship_for_move';
        $dict = array();
        $dict[':id_move'] = $id_move;
        $dict[':id_game_unit'] = $id_game_unit;
        SQLConnector::Singleton()->epp($query, $dict);

        return self::getSetShipsMove($id_game, $id_move);
    }

    /**
     * permenantly remove move (and corresponding ship) from database
     *
     * @param ModelSetShipsMove $move
     * @throws NullPointerException
     */
    public static function deleteSetShipsMove(ModelSetShipsMove $move) {
        $id_game = $move->getIdGame();
        $id_move = $move->getId();

        // 1. delete ship for move
        $query = 'delete_units_for_move';
        $dict = array();
        $dict[':id_move'] = $id_move;
        SQLConnector::Singleton()->epp($query, $dict);

        // 2. delete areas for move
        $query = 'delete_move_areas_for_move';
        SQLConnector::Singleton()->epp($query, $dict);

        // 3. delete move
        $query = 'delete_move';
        SQLConnector::Singleton()->epp($query, $dict);

        // 4. delete ship from DB
        ModelGameShip::deleteShip($id_game, $move->getIdGameUnit());

        unset(self::$moves[$id_game][$id_move]);
    }

    /**
     * @return int
     */
    public function getIdGameAreaInPort() {
        return $this->id_game_area_in_port;
    }

    /**
     * @return int
     */
    public function getIdGameArea() {
        return $this->id_game_area;
    }

    /**
     * @return int
     */
    public function getIdGameUnit() {
        return $this->id_game_unit;
    }

}
