<?php
namespace Attack\Model\Game;

use Attack\Database\SQLConnector;
use Attack\Exceptions\DatabaseException;
use Attack\Exceptions\NullPointerException;
use Attack\Tools\Iterator\ModelIterator;

class ModelTradeRoute {

    // phase models
    private static $traderoutes = array(); // array(int id => ModelTradeRoute)

    // member vars
    private $id; // int
    private $id_game; // int
    private $id_user; // int
    private $current_value; // int
    private $max_value; // int
    private $steps; // array(int step => int id_game_area)

    /**
     * creates the model
     *
     * @param $id int
     * @param $id_game int
     * @param $id_user int
     * @param $current_value int
     * @param $max_value int
     * @param $steps array(int step_nr => int id_game_area)
     */
    private function __construct($id, $id_game, $id_user, $current_value, $max_value, array $steps) {
        $this->id = $id;
        $this->id_game = $id_game;
        $this->id_user = $id_user;
        $this->current_value = $current_value;
        $this->max_value = $max_value;
        $this->steps = $steps;
    }

    /**
     * @param int $id
     * @return ModelTradeRoute
     * @throws DatabaseException
     * @throws NullPointerException
     */
    public static function getById($id) {
        if (isset(self::$traderoutes[$id])) {
            return self::$traderoutes[$id];
        }
        $query = 'get_traderoute_by_id';
        $dict = [];
        $dict[':id'] = $id;
        $result = SQLConnector::getInstance()->epp($query, $dict);
        if (empty($result)) {
            throw new NullPointerException('TradeRoute not found');
        }
        $id_game = 0;
        $id_user = 0;
        $current_value = 0;
        $max_value = 0;
        $steps = [];
        foreach ($result as $line) {
            $id_game = (int)$line['id_game'];
            $id_user = (int)$line['id_user'];
            $current_value = (int)$line['current_value'];
            $max_value = (int)$line['max_value'];
            $step = (int)$line['step'];
            $steps[$step] = (int)$line['id_game_area'];
        }

        $modelTradeRoute = new ModelTradeRoute($id, $id_game, $id_user, $current_value, $max_value, $steps);
        self::$traderoutes[$id] = $modelTradeRoute;
        return $modelTradeRoute;
    }

    /**
     * @param int $id_game
     * @param int|null $id_user
     * @return ModelIterator
     */
    public static function iterator($id_game, $id_user = null) {
        $query = 'get_traderoutes_by_game';
        $dict = [
            ':id_game' => $id_game
        ];
        if ($id_user !== null) {
            $query = 'get_traderoutes_by_game_and_user';
            $dict[':id_user'] = $id_user;
        }

        $result = SQLConnector::Singleton()->epp($query, $dict);
        $models = [];
        foreach ($result as $line) {
            $models[] = self::getById((int)$line['id']);
        }

        return new ModelIterator($models);

    }

    /**
     * @param int $id_game
     * @param int $id_user
     * @param int $current_value - starts with length of the traderoute, increment each round
     * @param int $max_value - maximum double the length of the traderoute
     * @param array $steps
     * @return ModelTradeRoute
     * @throws DatabaseException
     */
    public static function create($id_game, $id_user, $current_value, $max_value, array $steps) {
        SQLConnector::Singleton()->beginTransaction();

        try {
            // INSERT TRADEROUTE
            $query = 'insert_traderoute';
            $dict = [
                ':id_game' => $id_game,
                ':id_user' => $id_user,
                ':current_value' => $current_value,
                ':max_value' => $max_value
            ];
            SQLConnector::Singleton()->epp($query, $dict);
            $id = (int)SQLConnector::getInstance()->getLastInsertId();

            // INSERT STEPS
            $query = 'insert_area_for_traderoute';
            foreach ($steps as $step => $id_game_area) {
                $dict = [
                    ':id_game_traderoute' => $id,
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

        $modelTradeRoute = new ModelTradeRoute($id, $id_game, $id_user, $current_value, $max_value, $steps);
        self::$traderoutes[$id] = $modelTradeRoute;
        return $modelTradeRoute;
    }

    /**
     * @param ModelTradeRoute $modelTradeRoute
     * @return void
     * @throws DatabaseException
     */
    public static function delete(ModelTradeRoute $modelTradeRoute) {
        SQLConnector::Singleton()->beginTransaction();

        try {
            // DELETE TRADEROUTE AREAS
            $query = 'delete_traderoute_areas';
            $dict = [
                ':id_game_traderoute' => $modelTradeRoute->getId()
            ];
            SQLConnector::Singleton()->epp($query, $dict);

            // DELETE TRADEROUTE
            $query = 'delete_traderoute';
            $dict = [
                ':id' => $modelTradeRoute->getId()
            ];
            SQLConnector::Singleton()->epp($query, $dict);

            // COMMIT ALL QUERIES
            SQLConnector::Singleton()->commit();
        } catch (DatabaseException $ex) {
            SQLConnector::Singleton()->rollBack();
            throw $ex;
        }

        unset(self::$traderoutes[$modelTradeRoute->getId()]);
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getIdGame() {
        return $this->id_game;
    }

    /**
     * @return int
     */
    public function getIdUser() {
        return $this->id_user;
    }

    /**
     * @return int
     */
    public function getCurrentValue() {
        return $this->current_value;
    }

    /**
     * @param int $current_value
     * @throws DatabaseException
     */
    public function setCurrentValue($current_value) {
        $query = 'set_traderoute_value';
        $dict = [
            ':id' => $this->id,
            ':id_game_traderoute' => $current_value
        ];
        SQLConnector::Singleton()->epp($query, $dict);
        $this->current_value = $current_value;
    }

    /**
     * @return int
     */
    public function getMaxValue() {
        return $this->max_value;
    }

    /**
     * @return array
     */
    public function getSteps() {
        return $this->steps;
    }

}
