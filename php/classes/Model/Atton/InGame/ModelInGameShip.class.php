<?php
namespace AttOn\Model\Atton\InGame;

use AttOn\Exceptions\DataSourceException;
use AttOn\Exceptions\ModelException;
use AttOn\Exceptions\NullPointerException;
use AttOn\Model\Atton\Interfaces\ModelInGameUnit;
use AttOn\Model\Atton\ModelShip;
use AttOn\Model\DataBase\DataSource;
use AttOn\Model\DataBase\SQLCommands;
use AttOn\Model\Iterator\ModelIterator;
use AttOn\Model\User\ModelUser;

class ModelInGameShip extends ModelInGameUnit {

    private static $shipsById = array(); // array(int id_game => array(int id_zunit => ModelInGameShip))
    private static $shipsByName = array(); // array(int id_game => array(string name => ModelInGameShip))

    private $tank;
    private $hitpoints;
    private $name;
    private $experience;
    private $dive_status;
    private $id_zarea_in_port;

    /**
     * creates the model
     *
     * @param $id int
     * @param $id_user int
     * @param $id_game int
     * @param $id_unit int
     * @param $id_zarea int
     * @param $tank int
     * @param $hitpoints int
     * @param $name string
     * @param $experience int
     * @param $dive_status string - enum(up, diving, silent)
     * @param $id_zarea_in_port int
     * @return ModelInGameShip
     */
    protected function __construct($id, $id_user, $id_game, $id_unit, $id_zarea, $tank, $hitpoints, $name, $experience, $dive_status, $id_zarea_in_port) {
        parent::__construct($id, $id_user, $id_game, $id_unit, $id_zarea);
        $this->tank = intval($tank);
        $this->hitpoints = intval($hitpoints);
        $this->name = $name;
        $this->experience = intval($experience);
        $this->dive_status = $dive_status;
        $this->id_zarea_in_port = intval($id_zarea_in_port);
    }

    /**
     * returns ship with specific id
     *
     * @param $id_game int
     * @param $id int
     * @return ModelInGameShip
     * @throws NullPointerException
     */
    public static function getShipById($id_game, $id) {
        $id = intval($id);

        // check if model is already set
        if (isset(self::$shipsById[$id_game][$id])) {
            return self::$shipsById[$id_game][$id];
        }

        // if model not already set, load from database or create it
        SQLCommands::init($id_game);
        $query = 'get_ingame_ship_by_id';
        $dict = array();
        $dict[':id_zunit'] = $id;
        $result = DataSource::getInstance()->epp($query, $dict);
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
     * @return ModelInGameShip
     * @throws NullPointerException
     */
    public static function getShipByName($id_game, $name) {
        $id_game = intval($id_game);

        // check if model is already set
        if (isset(self::$shipsByName[$id_game][$name])) {
            return self::$shipsByName[$id_game][$name];
        }

        // if model not already set, load from database or create it
        SQLCommands::init($id_game);
        $query = 'get_ingame_ship_by_name';
        $dict = array();
        $dict[':name'] = $name;
        $result = DataSource::getInstance()->epp($query, $dict);
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
     * @param $id_zarea int
     * @return ModelIterator
     */
    public static function getShipsInAreaNotInPortByUser($id_user, $id_game, $id_zarea) {
        $models = array();
        $query = 'get_all_ships_in_area_not_in_port_by_user';
        $dict = array();
        $dict[':id_user'] = $id_user;
        $dict[':id_zarea'] = $id_zarea;

        // query units
        SQLCommands::init($id_game);
        $result = DataSource::Singleton()->epp($query, $dict);
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
     * @param $id_zarea_in_port int
     * @return ModelIterator
     */
    public static function getShipsInPortByUser($id_user, $id_game, $id_zarea_in_port) {
        $models = array();
        $query = 'get_all_ships_in_port_by_user';
        $dict = array();
        $dict[':id_user'] = $id_user;
        $dict[':id_zarea_in_port'] = $id_zarea_in_port;

        // query units
        SQLCommands::init($id_game);
        $result = DataSource::Singleton()->epp($query, $dict);
        foreach ($result as $ship) {
            $models[] = self::getShipById($id_game, (int)$ship['id']);
        }
        return new ModelIterator($models);
    }


    /**
     * returns all ships in area that are not destroyed
     *
     * @param $id_game int
     * @param $id_zarea int
     * @return ModelIterator
     */
    public static function getShipsInAreaNotInPort($id_game, $id_zarea) {
        $models = array();
        $query = 'get_all_ships_in_area_not_in_port';
        $dict = array();
        $dict[':id_zarea'] = $id_zarea;

        // query units
        SQLCommands::init($id_game);
        $result = DataSource::Singleton()->epp($query, $dict);
        foreach ($result as $ship) {
            $models[] = self::getShipById($id_game, (int)$ship['id']);
        }
        return new ModelIterator($models);
    }

    /**
     * returns all ships in area that are not destroyed
     *
     * @param $id_game int
     * @param $id_zarea_in_port int
     * @return ModelIterator
     */
    public static function getShipsInPort($id_game, $id_zarea_in_port) {
        $models = array();
        $query = 'get_all_ships_in_port';
        $dict = array();
        $dict[':id_zarea_in_port'] = $id_zarea_in_port;

        // query units
        SQLCommands::init($id_game);
        $result = DataSource::Singleton()->epp($query, $dict);
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
     * @param $id_zarea int
     * @param $name string
     * @param $id_zarea_in_port int
     * @return ModelInGameShip
     * @throws DataSourceException
     * @throws NullPointerException
     */
    public static function createShip($id_user, $id_game, $id_unit, $id_zarea, $name, $id_zarea_in_port) {
        $ship = ModelShip::getModelById($id_unit);

        SQLCommands::init($id_game);
        $query = 'create_ship';
        $dict = array();
        $dict[':tank'] = $ship->getTanksize();
        $dict[':hitpoints'] = $ship->getHitpoints();
        $dict[':name'] = $name;
        $dict[':experience'] = 0;
        $dict[':dive_status'] = ((int)$id_unit === ID_SUBMARINE) ? DIVE_STATUS_UP : null;
        $dict[':id_user'] = $id_user;
        $dict[':id_zarea'] = $id_zarea;
        $dict[':id_zarea_in_port'] = $id_zarea_in_port;
        $dict[':id_unit'] = $id_unit;
        DataSource::getInstance()->epp($query, $dict);
        $id = DataSource::getInstance()->getLastInsertId();

        return self::getShipById((int)$id_game, (int)$id);
    }

    /**
     * permanently delete ship from database
     *
     * @param $id_game
     * @param $id_zunit
     * @throws NullPointerException
     */
    public static function deleteShip($id_game, $id_zunit) {
        $id_game = (int)$id_game;
        $id_zunit = (int) $id_zunit;
        $ship = self::getShipById($id_game, $id_zunit);

        SQLCommands::init((int)$id_game);
        $query = 'delete_ship';
        $dict = array();
        $dict[':id_zunit'] = (int)$id_zunit;
        DataSource::getInstance()->epp($query, $dict);

        unset(self::$shipsById[$id_game][$id_zunit]);
        unset(self::$shipsByName[$id_game][$ship->getName()]);
    }

    private static function getShipHelper($id_game, array $result) {
        $unit_data = $result[0];
        $ship = new ModelInGameShip(
            (int)$unit_data['id'],
            (int)$unit_data['id_user'],
            $id_game,
            (int)$unit_data['id_unit'],
            (int)$unit_data['id_zarea'],
            (int)$unit_data['tank'],
            (int)$unit_data['hitpoints'],
            $unit_data['name'],
            (int)$unit_data['experience'],
            $unit_data['dive_status'],
            (int)$unit_data['id_zarea_in_port']
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
        return $this->id_zarea_in_port;
    }

    /**
     * @param int $id_user
     */
    public function setIdUser($id_user) {
        $id_user = intval($id_user);
        ModelUser::getUser($id_user);
        SQLCommands::init($this->id_game);
        $query = 'set_ship_user';
        $dict = array(':id_zunit' => $this->id, ':id_user' => $id_user);
        DataSource::getInstance()->epp($query, $dict);
        $this->id_user = $id_user;
    }

    /**
     * @param int $id_zarea
     */
    public function setIdZarea($id_zarea) {
        $id_zarea = intval($id_zarea);
        ModelGameArea::getGameArea($this->id_game, $id_zarea);
        SQLCommands::init($this->id_game);
        $query = 'set_ship_zarea';
        $dict = array(':id_zunit' => $this->id, ':id_zarea' => $id_zarea);
        DataSource::getInstance()->epp($query, $dict);
        $this->id_zarea = $id_zarea;
    }

    /**
     * @param int $tank
     */
    public function setTank($tank) {
        $ship = ModelShip::getModelById($this->id_unit);

        $tank = intval($tank);
        $tank = max($tank, 0);
        $tank = min($tank, $ship->getTanksize());
        SQLCommands::init($this->id_game);
        $query = 'set_ship_tank';
        $dict = array(':id_zunit' => $this->id, ':tank' => $tank);
        DataSource::getInstance()->epp($query, $dict);
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
        SQLCommands::init($this->id_game);
        $query = 'set_ship_hitpoints';
        $dict = array(':id_zunit' => $this->id, ':hitpoints' => $hitpoints);
        DataSource::getInstance()->epp($query, $dict);
        $this->hitpoints = $hitpoints;
    }

    /**
     * @param int $experience
     */
    public function setExperience($experience) {
        $experience = intval($experience);
        $experience = max($experience, 0);
        SQLCommands::init($this->id_game);
        $query = 'set_ship_experience';
        $dict = array(':id_zunit' => $this->id, ':experience' => $experience);
        DataSource::getInstance()->epp($query, $dict);
        $this->experience = $experience;
    }

    /**
     * @param string $dive_status
     */
    public function setDiveStatus($dive_status) {
        SQLCommands::init($this->id_game);
        $query = 'set_ship_dive_status';
        $dict = array(':id_zunit' => $this->id, ':dive_status' => $dive_status);
        DataSource::getInstance()->epp($query, $dict);
        $this->dive_status = $dive_status;
    }

    /**
     * @param int $id_zarea_in_port
     * @throws ModelException
     */
    public function setIdZareaInPort($id_zarea_in_port = null) {
        if ($id_zarea_in_port !== null) {
            $id_zarea_in_port = intval($id_zarea_in_port);
            $port_zarea = ModelGameArea::getGameArea($this->id_game, $id_zarea_in_port);
            $sea_zarea = ModelGameArea::getGameArea($this->id_game, $this->id_zarea);
            if (!in_array($port_zarea->getIdArea(), $sea_zarea->getAdjecents())) {
                throw new ModelException('Invalid port -> not adjacent to current area.');
            }
        }

        SQLCommands::init($this->id_game);
        $query = 'set_ship_in_port';
        $dict = array(':id_zunit' => $this->id, ':id_zarea_in_port' => $id_zarea_in_port);
        DataSource::getInstance()->epp($query, $dict);
        $this->id_zarea_in_port = $id_zarea_in_port;
    }

}
