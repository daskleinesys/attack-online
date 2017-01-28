<?php
namespace Attack\Model\Atton;

use Attack\Database\SQLConnector;
use Attack\Exceptions\NullPointerException;

class ModelStartShips {

    // startships models
    private static $models = array(); // array(int players => ModelStartShips)

    // member vars
    private $players; // int
    private $ships; // array(int id_unit => int numberof)

    /**
     * returns the startships model
     *
     * @param $players int
     * @param $ships array
     * @return ModelStartShips
     */
    private function __construct($players, array $ships) {
        $this->players = (int)$players;
        $this->ships = $ships;
    }

    /**
     * returns the specific model
     *
     * @param $players int
     * @return ModelStartShips
     * @throws NullPointerException
     */
    public static function getStartShipsForPlayers($players) {
        $players = (int)$players;
        if (isset(self::$models[$players])) {
            return self::$models[$players];
        }

        // check if there is a game
        $query = 'get_start_ships_by_players';
        $dict = array(':players' => $players);
        $result = SQLConnector::Singleton()->epp($query, $dict);
        if (empty($result)) {
            throw new NullPointerException('No startships for ' . $players . ' players found.');
        }
        $ships = array();
        foreach ($result as $line) {
            $ships[(int)$line['id_unit']] = (int)$line['numberof'];
        }

        return self::$models[$players] = new ModelStartShips($players, $ships);
    }

    /**
     * @return int
     */
    public function getPlayers() {
        return $this->players;
    }

    /**
     * @return array - array(int id_unit => int numberof)
     */
    public function getShips() {
        return $this->ships;
    }

}
