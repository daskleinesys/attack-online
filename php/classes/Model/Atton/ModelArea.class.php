<?php
namespace AttOn\Model\Atton;

use AttOn\Model\DataBase\DataSource;
use AttOn\Model\Iterator\ModelIterator;
use AttOn\Exceptions\NullPointerException;

class ModelArea {

    // phase models
    private static $areas = array(); // array(int id_area => ModelArea)

    // member vars
    private $id; // int
    private $name; // string
    private $number; // string
    private $coords_small; // string
    private $x; // int
    private $y; // int
    private $x2; // int
    private $y2; // int
    private $xres; // int
    private $yres; //int
    private $height; // int
    private $width; // int
    private $tanksize; // int
    private $id_type; // int
    private $zone; // int
    private $economy; // string/enum

    // dynamically loaded infos
    private $adjecents = array(); // array (int $id_area)

    /**
     * returns the area model
     *
     * @param $id_area int
     * @throws NullPointerException
     * @return ModelArea
     */
    private function __construct($id_area) {
        $id_area = intval($id_area);
        $this->id = $id_area;

        if (!$this->fill_member_vars()) {
            throw new NullPointerException('Area not found.');
        }
    }

    /**
     * returns the specific model
     *
     * @param $id_area int
     * @throws NullPointerException
     * @return ModelArea
     */
    public static function getArea($id_area) {
        if (isset(self::$areas[$id_area])) {
            return self::$areas[$id_area];
        }

        return self::$areas[$id_area] = new ModelArea($id_area);
    }

    /**
     * returns an iterator for areas
     *
     * @param $id_type int
     * @throws DataSourceException
     * @return ModelIterator
     */
    public static function iterator($id_type = null) {
        $models = array();
        $query = 'get_areas_for_type';
        $dict = array();
        $dict[':id_type'] = ($id_type == null) ? '%' : $id_type;

        // query phases
        $result = DataSource::Singleton()->epp($query, $dict);

        foreach ($result as $area) {
            $id_area = $area['id'];
            $models[] = self::getArea($id_area);
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
     * @return int
     */
    public function getNumber() {
        return $this->number;
    }

    /**
     * @return int
     */
    public function getIdType() {
        return $this->id_type;
    }

    /**
     * @return int
     */
    public function getZone() {
        return $this->zone;
    }

    /**
     * @return string/enum
     */
    public function getEconomy() {
        return $this->economy;
    }

    /**
     * @return array(int $id_adjacent_area)
     */
    public function getAdjecents() {
        if (!empty($this->adjecents)) {
            return $this->adjecents;
        }

        // load a2a
        $query = 'get_a2a';
        $dict = array(':id_area' => $this->id);
        $result = DataSource::getInstance()->epp($query, $dict);
        foreach ($result as $line) {
            $this->adjecents[] = $line['id_adjacent_area'];
        }

        return $this->adjecents;
    }

    private function fill_member_vars() {
        // check if there is a game
        $result = DataSource::Singleton()->epp('get_all_area_info', array(':id_area' => $this->id));
        if (empty($result)) {
            return false;
        }
        $data = $result[0];

        $this->name = $data['name'];
        $this->number = $data['number'];
        $this->coords_small = $data['coords_small'];
        $this->x = (int) $data['x'];
        $this->y = (int) $data['y'];
        $this->x2 = (int) $data['x2'];
        $this->y2 = (int) $data['y2'];
        $this->xres = (int) $data['xres'];
        $this->yres = (int) $data['yres'];
        $this->height = (int) $data['height'];
        $this->width = (int) $data['width'];
        $this->tanksize = (int) $data['tanksize'];
        $this->id_type = (int) $data['id_type'];
        $this->zone = (int) $data['zone'];
        $this->economy = $data['economy'];
        return true;
    }

}
