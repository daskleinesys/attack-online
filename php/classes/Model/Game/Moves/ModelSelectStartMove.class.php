<?php
namespace Attack\Model\Game\Moves;

use Attack\Model\Game\Moves\Interfaces\ModelMove;
use Attack\Exceptions\NullPointerException;
use Attack\Database\SQLConnector;
use Attack\Tools\Iterator\ModelIterator;
use Attack\Model\User\ModelUser;

class ModelSelectStartMove extends ModelMove {

    private static $moves = array(); // array(int id_game => array(int id_move => ModelSelectStartMove))

    private $regions = array(); // array(int option_number => array(int id_game_area))

    /**
     * creates the model
     *
     * @param $id_game int
     * @param $id_move int
     * @param $id_user int
     * @param $id_phase int
     * @param $round int
     * @param $deleted boolean
     * @param $regions array
     */
    protected function __construct($id_user, $id_game, $id_phase, $id_move, $round, $deleted, $regions) {
        parent::__construct($id_user, $id_game, $id_phase, $id_move, $round, $deleted);
        $this->regions = $regions;
    }

    /**
     * returns the corresponding model
     *
     * @param $id_game int
     * @param $id_move int
     * @throws NullPointerException. ModelException
     * @return ModelSelectStartMove
     */
    public static function getSelectStartMove($id_game, $id_move) {
        if (isset(self::$moves[$id_game][$id_move])) {
            return self::$moves[$id_game][$id_move];
        }

        $query = 'get_start_move';
        $dict = array();
        $dict[':id_move'] = intval($id_move);
        $result = SQLConnector::getInstance()->epp($query, $dict);
        if (empty($result)) {
            throw new NullPointerException('Move not found');
        }
        $regions = array();
        foreach ($result as $line) {
            if ($line['step'] === null) {
                continue;
            }
            if ($line['id_game_area'] === null) {
                continue;
            }
            if (!isset($regions[$line['step']])) {
                $regions[$line['step']] = array();
            }
            $regions[$line['step']][] = $line['id_game_area'];
        }
        return self::$moves[$id_game][$id_move] = new ModelSelectStartMove($result[0]['id_user'], $id_game, PHASE_SELECTSTART, $id_move, 0, $result[0]['deleted'], $regions);
    }

    /**
     * returns the corresponding model -> creates it if necessary
     *
     * @param $id_game int
     * @param $id_user int
     * @throws NullPointerException
     * @return ModelSelectStartMove
     */
    public static function getSelectStartMoveForUser($id_user, $id_game) {
        $query = 'get_start_move_for_user';
        $dict = array();
        $dict[':id_game'] = intval($id_game);
        $dict[':id_user'] = intval($id_user);
        $dict[':id_phase'] = PHASE_SELECTSTART;
        $dict[':round'] = 0;
        $result = SQLConnector::getInstance()->epp($query, $dict);
        ModelUser::getUser($id_user);
        if (empty($result)) {
            $id_move = self::createSelectStartMove($id_user, $id_game);
        } else {
            $id_move = $result[0]['id'];
        }

        return self::getSelectStartMove($id_game, $id_move);
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
        $dict[':id_phase'] = PHASE_SELECTSTART;
        $dict[':round'] = 0;

        $result = SQLConnector::Singleton()->epp($query, $dict);
        $moves = array();
        foreach ($result as $move) {
            $id_move = $move['id'];
            $moves[] = self::getSelectStartMove($id_game, $id_move);
        }

        return new ModelIterator($moves);
    }

    /**
     * creates the start move for user, and returns the id of the new move
     *
     * @param $id_game int
     * @param $id_user int
     * @return int
     */
    private static function createSelectStartMove($id_user, $id_game) {
        $query = 'insert_move';
        $dict = array();
        $dict[':id_game'] = intval($id_game);
        $dict[':id_user'] = intval($id_user);
        $dict[':id_phase'] = PHASE_SELECTSTART;
        $dict[':round'] = 0;
        SQLConnector::Singleton()->epp($query, $dict);
        $id_move = SQLConnector::getInstance()->getLastInsertId();
        return (int)$id_move;
    }

    /**
     * updates selected areas if necessary
     *
     * @param $option_number int
     * @param $zareas array(int id_game_area)
     * @return void
     */
    public function setRegions($option_number, $zareas) {
        $option_number = intval($option_number);

        // check if areas are already set
        if (isset($this->regions[$option_number])) {
            $found_all = true;
            foreach ($zareas as $id_game_area) {
                if (!in_array($id_game_area, $this->regions[$option_number])) {
                    $found_all = false;
                }
            }
            if ($found_all) {
                return;
            }
        }

        $dict = array();
        $dict[':id_move'] = $this->id;
        $dict[':step'] = $option_number;

        if (isset($this->regions[$option_number])) {
            unset($this->regions[$option_number]);
            $query = 'delete_move_areas_for_step';
            SQLConnector::Singleton()->epp($query, $dict);
        }

        // insert areas
        $query = 'insert_area_for_move';
        foreach ($zareas as $id_game_area) {
            $dict[':id_game_area'] = $id_game_area;
            SQLConnector::getInstance()->epp($query, $dict);
        }
        $this->regions[$option_number] = $zareas;
    }

    /**
     * return true if country is already selected
     *
     * @param $option_number int
     * @param $id_game_area int
     * @return bool
     */
    public function checkIfAreaIsSelected($option_number, $id_game_area) {
        if (!isset($this->regions[$option_number])) {
            return false;
        }
        if (in_array($id_game_area, $this->regions[$option_number])) {
            return true;
        }
        return false;
    }

    /**
     * @return array(int option_number => array(int id_game_area))
     */
    public function getRegions() {
        return $this->regions;
    }

}
