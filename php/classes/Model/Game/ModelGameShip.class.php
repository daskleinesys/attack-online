<?php
namespace Attack\Model\Game;

use Attack\Exceptions\DatabaseException;
use Attack\Exceptions\ModelException;
use Attack\Exceptions\NullPointerException;
use Attack\Model\Units\Interfaces\ModelGameUnit;
use Attack\Model\Units\ModelShip;
use Attack\Database\SQLConnector;
use Attack\Tools\Iterator\ModelIterator;
use Attack\Model\User\ModelUser;

class ModelGameShip extends ModelGameUnit {

    private static $shipsById = array(); // array(int id_game => array(int id_game_unit => ModelGameShip))
    private static $shipsByName = array(); // array(int id_game => array(string name => ModelGameShip))

    private $tank;
    private $hitpoints;
    private $name;
    private $experience;
    private $dive_status;
    private $id_game_area_in_port;

    /**
     * creates the model
     *
     * @param $id int
     * @param $id_user int
     * @param $id_game int
     * @param $id_unit int
     * @param $id_game_area int
     * @param $tank int
     * @param $hitpoints int
     * @param $name string
     * @param $experience int
     * @param $dive_status string - enum(up, diving, silent)
     * @param $id_game_area_in_port int
     */
    protected function __construct($id, $id_user, $id_game, $id_unit, $id_game_area, $tank, $hitpoints, $name, $experience, $dive_status, $id_game_area_in_port) {
        parent::__construct($id, $id_user, $id_game, $id_unit, $id_game_area);
        $this->tank = intval($tank);
        $this->hitpoints = intval($hitpoints);
        $this->name = $name;
        $this->experience = intval($experience);
        $this->dive_status = $dive_status;
        $this->id_game_area_in_port = intval($id_game_area_in_port);
    }

    /**
     * returns ship with specific id
     *
     * @param $id_game int
     * @param $id int
     * @return ModelGameShip
     * @throws NullPointerException
     */
    public static function getShipById($id_game, $id) {
        $id_game = intval($id_game);
        $id = intval($id);

        // check if model is already set
        if (isset(self::$shipsById[$id_game][$id])) {
            return self::$shipsById[$id_game][$id];
        }

        // if model not already set, load from database or create it
        $query = 'get_game_ship_by_id';
        $dict = array();
        $dict[':id_game_unit'] = $id;
        $result = SQLConnector::getInstance()->epp($query, $dict);
        if (empty($result)) {
            throw new NullPointerException("No ship by id $id found.");
        }
        return self::getShipHelper($id_game, $result);
    }

    /**
     * returns ship with specific name
     *
     * @param $id_game int
     * @param $name string
     * @return ModelGameShip
     * @throws NullPointerException
     */
    public static function getShipByName($id_game, $name) {
        $id_game = intval($id_game);

        // check if model is already set
        if (isset(self::$shipsByName[$id_game][$name])) {
            return self::$shipsByName[$id_game][$name];
        }

        // if model not already set, load from database or create it
        $query = 'get_game_ship_by_name';
        $dict = array();
        $dict[':name'] = $name;
        $result = SQLConnector::getInstance()->epp($query, $dict);
        if (empty($result)) {
            throw new NullPointerException("No ship by name $name found.");
        }
        return self::getShipHelper($id_game, $result);
    }

    /**
     * returns all ship area/user that are not destroyed
     *
     * @param $id_user int
     * @param $id_game int
     * @param $id_game_area int
     * @return ModelIterator
     */
    public static function getShipsInAreaNotInPortByUser($id_user, $id_game, $id_game_area) {
        $models = array();
        $query = 'get_all_game_ships_not_in_port_by_area_user';
        $dict = array();
        $dict[':id_user'] = $id_user;
        $dict[':id_game_area'] = $id_game_area;

        // query units
        $result = SQLConnector::Singleton()->epp($query, $dict);
        foreach ($result as $ship) {
            $models[] = self::getShipById($id_game, (int)$ship['id']);
        }
        return new ModelIterator($models);
    }

    /**
     * returns all ship area/user that are not destroyed
     *
     * @param $id_user int
     * @param $id_game int
     * @param $id_game_area_in_port int
     * @return ModelIterator
     */
    public static function getShipsInPortByUser($id_user, $id_game, $id_game_area_in_port) {
        $models = array();
        $query = 'get_all_game_ships_by_port_user';
        $dict = array();
        $dict[':id_user'] = $id_user;
        $dict[':id_game_area_in_port'] = $id_game_area_in_port;

        // query units
        $result = SQLConnector::Singleton()->epp($query, $dict);
        foreach ($result as $ship) {
            $models[] = self::getShipById($id_game, (int)$ship['id']);
        }
        return new ModelIterator($models);
    }


    /**
     * returns all ships in area that are not destroyed
     *
     * @param $id_game int
     * @param $id_game_area int
     * @return ModelIterator
     */
    public static function getShipsInAreaNotInPort($id_game, $id_game_area) {
        $models = array();
        $query = 'get_all_game_ships_not_in_port_by_area';
        $dict = array();
        $dict[':id_game_area'] = $id_game_area;

        // query units
        $result = SQLConnector::Singleton()->epp($query, $dict);
        foreach ($result as $ship) {
            $models[] = self::getShipById($id_game, (int)$ship['id']);
        }
        return new ModelIterator($models);
    }

    /**
     * returns all ships in area that are not destroyed
     *
     * @param $id_game int
     * @param $id_game_area_in_port int
     * @return ModelIterator
     */
    public static function getShipsInPort($id_game, $id_game_area_in_port) {
        $models = array();
        $query = 'get_all_ships_by_port';
        $dict = array();
        $dict[':id_game_area_in_port'] = $id_game_area_in_port;

        // query units
        $result = SQLConnector::Singleton()->epp($query, $dict);
        foreach ($result as $ship) {
            $models[] = self::getShipById($id_game, (int)$ship['id']);
        }
        return new ModelIterator($models);
    }

    /**
     * creates ship
     *
     * @param $id_user int
     * @param $id_game int
     * @param $id_unit int
     * @param $id_game_area int
     * @param $name string
     * @param $id_game_area_in_port int
     * @return ModelGameShip
     * @throws DatabaseException
     * @throws NullPointerException
     */
    public static function createShip($id_user, $id_game, $id_unit, $id_game_area, $name, $id_game_area_in_port) {
        $ship = ModelShip::getModelById($id_unit);

        $query = 'insert_game_ship';
        $dict = array();
        $dict[':tank'] = $ship->getTanksize();
        $dict[':hitpoints'] = $ship->getHitpoints();
        $dict[':name'] = $name;
        $dict[':experience'] = 0;
        $dict[':dive_status'] = ((int)$id_unit === ID_SUBMARINE) ? DIVE_STATUS_UP : null;
        $dict[':id_game'] = $id_game;
        $dict[':id_user'] = $id_user;
        $dict[':id_game_area'] = ($id_game_area === NO_AREA) ? null : $id_game_area;
        $dict[':id_game_area_in_port'] = ($id_game_area_in_port === NO_AREA) ? null : $id_game_area_in_port;
        $dict[':id_unit'] = $id_unit;
        SQLConnector::getInstance()->epp($query, $dict);
        $id = SQLConnector::getInstance()->getLastInsertId();

        return self::getShipById((int)$id_game, (int)$id);
    }

    /**
     * permanently delete ship from database
     *
     * @param $id_game
     * @param $id_game_unit
     * @throws NullPointerException
     */
    public static function deleteShip($id_game, $id_game_unit) {
        $id_game = (int)$id_game;
        $id_game_unit = (int) $id_game_unit;
        $ship = self::getShipById($id_game, $id_game_unit);

        $query = 'delete_game_ship';
        $dict = array();
        $dict[':id_game_unit'] = (int)$id_game_unit;
        SQLConnector::getInstance()->epp($query, $dict);

        unset(self::$shipsById[$id_game][$id_game_unit]);
        unset(self::$shipsByName[$id_game][$ship->getName()]);
    }

    private static function getShipHelper($id_game, array $result) {
        $unit_data = $result[0];
        $ship = new ModelGameShip(
            (int)$unit_data['id'],
            (int)$unit_data['id_user'],
            $id_game,
            (int)$unit_data['id_unit'],
            (int)$unit_data['id_game_area'],
            (int)$unit_data['tank'],
            (int)$unit_data['hitpoints'],
            $unit_data['name'],
            (int)$unit_data['experience'],
            $unit_data['dive_status'],
            (int)$unit_data['id_game_area_in_port']
        );
        self::$shipsById[$id_game][$ship->getId()] = $ship;
        return self::$shipsByName[$id_game][$ship->getName()] = $ship;
    }

    /**
     * @return int
     */
    public function getTank() {
        return $this->tank;
    }

    /**
     * @return int
     */
    public function getHitpoints() {
        return $this->hitpoints;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getExperience() {
        return $this->experience;
    }

    /**
     * @return string
     */
    public function getDiveStatus() {
        return $this->dive_status;
    }

    /**
     * @return int
     */
    public function getIdZareaInPort() {
        return $this->id_game_area_in_port;
    }

    /**
     * @param int $id_user
     */
    public function setIdUser($id_user) {
        $id_user = intval($id_user);
        ModelUser::getUser($id_user);
        $query = 'set_game_ship_user';
        $dict = array(':id_game_unit' => $this->id, ':id_user' => $id_user);
        SQLConnector::getInstance()->epp($query, $dict);
        $this->id_user = $id_user;
    }

    /**
     * @param int $id_game_area
     */
    public function setIdZarea($id_game_area) {
        $id_game_area = intval($id_game_area);
        ModelGameArea::getGameArea($this->id_game, $id_game_area);
        $query = 'set_game_ship_game_area';
        $dict = array(':id_game_unit' => $this->id, ':id_game_area' => $id_game_area);
        SQLConnector::getInstance()->epp($query, $dict);
        $this->id_game_area = $id_game_area;
    }

    /**
     * @param int $tank
     */
    public function setTank($tank) {
        $ship = ModelShip::getModelById($this->id_unit);

        $tank = intval($tank);
        $tank = max($tank, 0);
        $tank = min($tank, $ship->getTanksize());
        $query = 'set_game_ship_tank';
        $dict = array(':id_game_unit' => $this->id, ':tank' => $tank);
        SQLConnector::getInstance()->epp($query, $dict);
        $this->tank = $tank;
    }

    /**
     * @param int $hitpoints
     */
    public function setHitpoints($hitpoints) {
        $ship = ModelShip::getModelById($this->id_unit);

        $hitpoints = intval($hitpoints);
        $hitpoints = max($hitpoints, 0);
        $hitpoints = min($hitpoints, $ship->getHitpoints());
        $query = 'set_game_ship_hitpoints';
        $dict = array(':id_game_unit' => $this->id, ':hitpoints' => $hitpoints);
        SQLConnector::getInstance()->epp($query, $dict);
        $this->hitpoints = $hitpoints;
    }

    /**
     * @param int $experience
     */
    public function setExperience($experience) {
        $experience = intval($experience);
        $experience = max($experience, 0);
        $query = 'set_game_ship_experience';
        $dict = array(':id_game_unit' => $this->id, ':experience' => $experience);
        SQLConnector::getInstance()->epp($query, $dict);
        $this->experience = $experience;
    }

    /**
     * @param string $dive_status
     */
    public function setDiveStatus($dive_status) {
        $query = 'set_game_ship_dive_status';
        $dict = array(':id_game_unit' => $this->id, ':dive_status' => $dive_status);
        SQLConnector::getInstance()->epp($query, $dict);
        $this->dive_status = $dive_status;
    }

    /**
     * @param int $id_game_area_in_port
     * @throws ModelException
     */
    public function setIdZareaInPort($id_game_area_in_port = null) {
        if ($id_game_area_in_port !== null) {
            $id_game_area_in_port = intval($id_game_area_in_port);
            $port_zarea = ModelGameArea::getGameArea($this->id_game, $id_game_area_in_port);
            $sea_zarea = ModelGameArea::getGameArea($this->id_game, $this->id_game_area);
            if (!in_array($port_zarea->getIdArea(), $sea_zarea->getAdjecents())) {
                throw new ModelException('Invalid port -> not adjacent to current area.');
            }
        }

        $query = 'set_game_ship_port';
        $dict = array(':id_game_unit' => $this->id, ':id_game_area_in_port' => $id_game_area_in_port);
        SQLConnector::getInstance()->epp($query, $dict);
        $this->id_game_area_in_port = $id_game_area_in_port;
    }

}
