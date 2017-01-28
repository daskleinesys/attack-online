<?php
namespace Attack\Model\Atton;

use Attack\Database\SQLConnector;
use Attack\Exceptions\DatabaseException;
use Attack\Model\Iterator\ModelIterator;
use Attack\Exceptions\NullPointerException;

class ModelStartingSet {

    // phase models
    private static $sets = array(); // array(int id_set => ModelStartingSet)

    // member vars
    private $id; // int
    private $name; // string
    private $players; // int

    /**
     * returns the starting-set model
     *
     * @param $id_set int
     * @throws NullPointerException
     * @return ModelStartingSet
     */
    private function __construct($id_set) {
        $this->id = intval($id_set);

        if (!$this->fill_member_vars()) {
            throw new NullPointerException('Starting Set not found.');
        }
    }

    /**
     * returns the specific model
     *
     * @param $id_color int
     * @throws NullPointerException
     * @return ModelStartingSet
     */
    public static function getSet($id_set) {
        if (isset(self::$sets[$id_set])) {
            return self::$sets[$id_set];
        }

        return self::$sets[$id_set] = new ModelStartingSet($id_set);
    }

    /**
     * returns an iterator for all start sets
     *
     * @param null $players
     * @param bool $random_order
     * @return ModelIterator
     * @throws NullPointerException
     */
    public static function iterator($players, $random_order = false) {
        $models = array();
        $query = 'get_all_start_sets';
        $dict = array();
        $dict[':players'] = intval($players);

        // query phases
        $result = SQLConnector::Singleton()->epp($query, $dict);

        if (empty($result)) {
            throw new NullPointerException('This number of players is not supported.');
        }

        foreach ($result as $set) {
            $id_set = $set['id'];
            $models[] = self::getSet($id_set);
        }

        if ($random_order) {
            shuffle($models);
        }

        return new ModelIterator($models);
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPlayers() {
        return $this->players;
    }

    private function fill_member_vars() {
        // check if there is a game
        $result = SQLConnector::Singleton()->epp('get_start_set_by_id', array(':id_set' => $this->id));
        if (empty($result)) {
            return false;
        }
        $data = $result[0];

        $this->name = $data['name'];
        $this->players = $data['players'];
        return true;
    }

}
