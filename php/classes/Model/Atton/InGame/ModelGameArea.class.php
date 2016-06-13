<?php
namespace AttOn\Model\Atton\InGame;

use AttOn\Model\Atton\ModelArea;
use AttOn\Model\DataBase\DataSource;
use AttOn\Model\DataBase\SQLCommands;
use AttOn\Model\Iterator\ModelIterator;
use AttOn\Exceptions\NullPointerException;

class ModelGameArea {

    // phase models
    private static $game_areas = array(); // array(int id_game => array(int id_game_area => ModelGameArea))
    private static $game_areas_for_area = array(); // array(int id_game => array(int id_area => ModelGameArea))

    // member vars
    private $id_game; // int
    private $id; // int
    private $tank; // int
    private $id_user; // int
    private $id_area; // int
    private $id_resource; // int
    private $productivity; // int

    /**
     * returns the game area model
     *
     * @param $id_game int
     * @param $id_game_area int
     * @throws NullPointerException
     * @return ModelGameArea
     */
    private function __construct($id_game, $id_game_area) {
        $this->id_game = intval($id_game);
        $this->id = intval($id_game_area);

        if (!$this->fill_member_vars()) {
            throw new NullPointerException('GameArea not found.');
        }
    }

    /**
     * returns the specific model
     *
     * @param $id_game int
     * @param $id_game_area int
     * @throws NullPointerException
     * @return ModelGameArea
     */
    public static function getGameArea($id_game, $id_game_area) {
        $id_game = intval($id_game);
        $id_game_area = intval($id_game_area);
        if (isset(self::$game_areas[$id_game][$id_game_area])) {
            return self::$game_areas[$id_game][$id_game_area];
        }

        $area = new ModelGameArea($id_game, $id_game_area);
        self::$game_areas[$id_game][$id_game_area] = $area;
        self::$game_areas_for_area[$id_game][$area->getIdArea()] = $area;

        return $area;
    }

    /**
     * returns the specific model for corresponding area
     *
     * @param $id_game int
     * @param $id_area int
     * @throws NullPointerException
     * @return ModelGameArea
     */
    public static function getGameAreaForArea($id_game, $id_area) {
        $id_game = intval($id_game);
        $id_area = intval($id_area);
        if (isset(self::$game_areas_for_area[$id_game][$id_area])) {
            return self::$game_areas_for_area[$id_game][$id_area];
        }

        SQLCommands::init($id_game);
        $query = 'get_zarea_for_area';
        $dict = array(':id_area' => $id_area);
        $result = DataSource::getInstance()->epp($query, $dict);
        if (empty($result)) {
            throw new NullPointerException('No corresponding area found.');
        }
        $id_game_area = $result[0]['id'];

        return self::getGameArea($id_game, $id_game_area);
    }

    /**
     * returns an iterator for game areas
     *
     * @param $id_user int
     * @param $id_game int
     * @throws DataSourceException
     * @return ModelIterator
     */
    public static function iterator($id_user = null, $id_game) {
        $id_game = intval($id_game);
        SQLCommands::init($id_game);

        $models = array();
        $query = 'get_zareas';
        $dict = array();
        $dict[':id_user'] = ($id_user == null) ? '%' : intval($id_user);

        // query phases
        $result = DataSource::Singleton()->epp($query,$dict);

        foreach ($result as $area) {
            $id_game_area = $area['id'];
            $models[] = self::getGameArea($id_game,$id_game_area);
        }

        return new ModelIterator($models);
    }

    /**
     * checks if there is an game-area for the area given
     *
     * @param $id_game int
     * @param $id_area int
     * @return boolean
     */
    public static function checkArea($id_game, $id_area) {
        try {
            self::getGameAreaForArea($id_game, $id_area);
            return true;
        } catch (NullPointerException $ex) {
            return false;
        }
    }

    /**
     * creates a game-area or updates all infos if already created
     *
     * @param $id_game int
     * @param $tank int
     * @param $id_user int
     * @param $id_area int
     * @param $id_resource
     * @param $productivity int
     * @return ModelGameArea
     */
    public static function setGameArea($id_game, $tank, $id_user, $id_area, $id_resource, $productivity) {
        $id_game = intval($id_game);
        $tank = intval($tank);
        $id_user = intval($id_user);
        $id_area = intval($id_area);
        $id_resource = intval($id_resource);
        $productivity = intval($productivity);

        if (self::checkArea($id_game, $id_area)) {
            $area = self::getGameAreaForArea($id_game, $id_area);
            $area->setTank($tank);
            $area->setIdUser($id_user);
            $area->setIdResource($id_resource);
            $area->setProductivity($productivity);
            return $area;
        } else {
            SQLCommands::init($id_game);
            $query = 'create_zarea';
            $dict = array();
            $dict[':tank'] = $tank;
            $dict[':id_user'] = $id_user;
            $dict[':id_area'] = $id_area;
            $dict[':id_resource'] = $id_resource;
            $dict[':productivity'] = $productivity;
            DataSource::getInstance()->epp($query, $dict);
            return self::getGameAreaForArea($id_game, $id_area);
        }
    }

    /**
     * sets the tank
     *
     * @return void
     */
    public function setTank($tank) {
        SQLCommands::init($this->id_game);
        $tank = intval($tank);
        $query = 'update_zarea_tank';
        $dict = array();
        $dict[':id_zarea'] = $this->id;
        $dict[':tank'] = $tank;
        $this->tank = $tank;
        DataSource::Singleton()->epp($query, $dict);
    }

    /**
     * sets the user that owns this game-area
     *
     * @return void
     */
    public function setIdUser($id_user) {
        SQLCommands::init($this->id_game);
        $id_user = intval($id_user);
        $query = 'update_zarea_id_user';
        $dict = array();
        $dict[':id_zarea'] = $this->id;
        $dict[':id_user'] = $id_user;
        DataSource::Singleton()->epp($query, $dict);
        $this->id_user = $id_user;
    }

    /**
     * sets the resource of this game-area
     *
     * @return void
     */
    public function setIdResource($id_resource) {
        SQLCommands::init($this->id_game);
        $id_resource = intval($id_resource);
        $query = 'update_zarea_id_resource';
        $dict = array();
        $dict[':id_zarea'] = $this->id;
        $dict[':id_resource'] = $id_resource;
        $this->id_resource = $id_resource;
        DataSource::Singleton()->epp($query, $dict);

    }

    /**
     * sets the productivity of this game-area
     *
     * @return void
     */
    public function setProductivity($productivity) {
        SQLCommands::init($this->id_game);
        $productivity = intval($productivity);
        $query = 'update_zarea_productivity';
        $dict = array();
        $dict[':id_zarea'] = $this->id;
        $dict[':productivity'] = $productivity;
        $this->productivity = $productivity;
        DataSource::Singleton()->epp($query, $dict);

    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return ModelArea
     */
     public function getArea() {
         return ModelArea::getArea($this->id_area);
     }

    /**
     * @return string
     */
    public function getName() {
        $area = ModelArea::getArea($this->id_area);
        return $area->getName();
    }

    /**
     * @return int
     */
    public function getNumber() {
        $area = ModelArea::getArea($this->id_area);
        return $area->getNumber();
    }

    /**
     * @return int
     */
    public function getIdType() {
        $area = ModelArea::getArea($this->id_area);
        return $area->getIdType();
    }

    /**
     * @return int
     */
    public function getZone() {
        $area = ModelArea::getArea($this->id_area);
        return $area->getZone();
    }

    /**
     * @return string/enum
     */
    public function getEconomy() {
        $area = ModelArea::getArea($this->id_area);
        return $area->getEconomy();
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
    public function getIdUser() {
        return $this->id_user;
    }

    /**
     * @return int
     */
    public function getIdArea() {
        return $this->id_area;
    }

    /**
     * @return int
     */
    public function getIdResource() {
        return $this->id_resource;
    }

    /**
     * @return int
     */
    public function getProductivity() {
        return $this->productivity;
    }

    /**
     * @return array(int $id_adjacent_area)
     */
    public function getAdjecents() {
        $area = ModelArea::getArea($this->id_area);
        return $area->getAdjecents();
    }

    private function fill_member_vars() {
        SQLCommands::init($this->id_game);
        // check if there is a game
        $result = DataSource::Singleton()->epp('get_all_zarea_info', array(':id_zarea' => $this->id));
        if (empty($result)) {
            return false;
        }
        $data = $result[0];

        $this->tank = (int) $data['tank'];
        $this->id_user = (int) $data['id_user'];
        $this->id_area = (int) $data['id_area'];
        $this->id_resource = (int) $data['id_resource'];
        $this->productivity = (int) $data['productivity'];
        return true;
    }

}
