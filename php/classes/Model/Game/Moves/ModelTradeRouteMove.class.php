<?php
namespace Attack\Model\Game\Moves;

use Attack\Exceptions\DatabaseException;
use Attack\Model\Game\Moves\Interfaces\ModelMove;
use Attack\Exceptions\NullPointerException;
use Attack\Database\SQLConnector;
use Attack\Tools\Iterator\ModelIterator;

class ModelTradeRouteMove extends ModelMove {

    private static $moves = array(); // array(int id_move => ModelTradeRouteMove)

    private $steps = array(); // array(int step_nr => int id_game_area)

    /**
     * creates the model
     * if (count(steps) === 2) --> this move deletes a traderoute
     * otherwise this move creates a new traderoute
     *
     * @param $id_user int
     * @param $id_game int
     * @param $id_phase int
     * @param $id_move int
     * @param $round int
     * @param $deleted boolean
     * @param $steps array(int step_nr => int id_game_area)
     */
    protected function __construct($id_user, $id_game, $id_phase, $id_move, $round, $deleted, $steps) {
        parent::__construct($id_user, $id_game, $id_phase, $id_move, $round, $deleted);
        $this->steps = $steps;
    }

    /**
     * returns the corresponding model
     *
     * @param $id_move int
     * @throws NullPointerException
     * @return ModelTradeRouteMove
     */
    public static function getById($id_move) {
        if (isset(self::$moves[$id_move])) {
            return self::$moves[$id_move];
        }

        $query = 'get_traderoute_move';
        $dict = [
            ':id_move' => $id_move
        ];
        $result = SQLConnector::getInstance()->epp($query, $dict);
        if (empty($result)) {
            throw new NullPointerException('Move not found');
        }
        $id_user = (int)$result[0]['id_user'];
        $id_game = (int)$result[0]['id_game'];
        $round = (int)$result[0]['round'];
        $deleted = (bool)$result[0]['deleted'];
        $steps = array();
        foreach ($result as $line) {
            $step_nr = (int)$line['step'];
            $id_game_area = (int)$line['id_game_area'];
            $steps[$step_nr] = $id_game_area;
        }
        $move = new ModelTradeRouteMove($id_user, $id_game, PHASE_TRADEROUTES, $id_move, $round, $deleted, $steps);
        self::$moves[$id_move] = $move;
        return $move;
    }

    /**
     * returns an iterator for traderoute moves, specify round and/or user if necessary
     *
     * @param $id_user int
     * @param $id_game int
     * @param $round int
     * @return ModelIterator
     */
    public static function iterator($id_user = null, $id_game, $round = null) {
        $query = 'get_game_moves_by_phase_round_user';
        $dict = [
            ':id_game' => $id_game,
            ':id_phase' => PHASE_TRADEROUTES,
            ':round' => ($round == null) ? '%' : $round
        ];
        if ($id_user == null) {
            $query = 'get_game_moves_by_phase_round';
        } else {
            $dict[':id_user'] = $id_user;
        }

        $result = SQLConnector::Singleton()->epp($query, $dict);
        $moves = array();
        foreach ($result as $move) {
            $moves[] = self::getById((int)$move['id']);
        }

        return new ModelIterator($moves);
    }

    /**
     * creates land move for user
     *
     * @param $id_user int
     * @param $id_game int
     * @param $round int
     * @param $steps array(int step_nr => int id_game_area) -> step_nr counting from 0 to x
     * @throws DatabaseException
     * @return ModelTradeRouteMove
     */
    public static function create($id_user, $id_game, $round, $steps) {
        SQLConnector::Singleton()->beginTransaction();

        // CREATE MOVE
        $query = 'insert_move';
        $dict = [
            ':id_user' => $id_user,
            ':id_game' => $id_game,
            ':id_phase' => PHASE_TRADEROUTES,
            ':round' => $round
        ];
        SQLConnector::Singleton()->epp($query, $dict);
        $id_move = SQLConnector::getInstance()->getLastInsertId();

        try {
            // INSERT MOVE STEPS
            $query = 'insert_area_for_move';
            foreach ($steps as $step => $id_game_area) {
                $dict = [
                    ':id_move' => $id_move,
                    ':id_game_area' => $id_game_area,
                    ':step' => $step
                ];
                SQLConnector::Singleton()->epp($query, $dict);
            }

            // COMMIT ALL QUERIES
            SQLConnector::Singleton()->commit();
        } catch (DatabaseException $ex) {
            SQLConnector::Singleton()->rollBack();
            throw $ex;
        }

        $move = new ModelTradeRouteMove($id_user, $id_game, PHASE_TRADEROUTES, $id_move, $round, false, $steps);
        self::$moves[$id_move] = $move;
        return $move;
    }

    /**
     * delete move from database
     *
     * @param ModelTradeRouteMove $move
     * @throws DatabaseException
     */
    public static function delete(ModelTradeRouteMove $move) {
        SQLConnector::Singleton()->beginTransaction();

        try {
            // DELETE STEPS
            $query = 'delete_move_areas_for_move';
            $dict = [
                ':id_move' => $move->getId()
            ];
            SQLConnector::Singleton()->epp($query, $dict);

            // DELETE MOVE
            $query = 'delete_move';
            SQLConnector::Singleton()->epp($query, $dict);

            // COMMIT ALL QUERIES
            SQLConnector::Singleton()->commit();
        } catch (DatabaseException $ex) {
            SQLConnector::Singleton()->rollBack();
            throw $ex;
        }

        unset(self::$moves[$move->getId()]);
    }

    /**
     * @return array(int step_nr => int id_game_area)
     */
    public function getSteps() {
        return $this->steps;
    }

}
