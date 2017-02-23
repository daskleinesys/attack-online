<?php
namespace Attack\Model\Areas;

use Attack\Database\SQLConnector;
use Attack\Exceptions\DatabaseException;
use Attack\Tools\Iterator\ModelIterator;
use Attack\Exceptions\NullPointerException;

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
    private $id_type; // int
    private $economy; // string/enum

    // dynamically loaded infos
    private $adjecents = array(); // array (int $id_area)

    /**
     * returns the area model
     *
     * @param $id_area int
     * @throws NullPointerException
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
     * @return ModelIterator
     * @throws DatabaseException
     */
    public static function iterator($id_type = null) {
        $models = array();
        $query = 'get_all_areas';
        $dict = array();
        if ($id_type != null) {
            $query = 'get_areas_by_type';
            $dict[':id_type'] = $id_type;
        }

        // query phases
        $result = SQLConnector::Singleton()->epp($query, $dict);

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

        // load adjacent_areas
        $query = 'get_adjacent_areas_for_area';
        $dict = array(':id_area' => $this->id);
        $result = SQLConnector::getInstance()->epp($query, $dict);
        foreach ($result as $line) {
            $this->adjecents[] = $line['id_adjacent_area'];
        }

        return $this->adjecents;
    }

    private function fill_member_vars() {
        // check if there is a game
        $result = SQLConnector::Singleton()->epp('get_area_by_id', array(':id_area' => $this->id));
        if (empty($result)) {
            return false;
        }
        $data = $result[0];

        $this->name = $data['name'];
        $this->number = $data['number'];
        $this->coords_small = $data['coords_small'];
        $this->x = (int)$data['x'];
        $this->y = (int)$data['y'];
        $this->x2 = (int)$data['x2'];
        $this->y2 = (int)$data['y2'];
        $this->xres = (int)$data['xres'];
        $this->yres = (int)$data['yres'];
        $this->height = (int)$data['height'];
        $this->width = (int)$data['width'];
        $this->id_type = (int)$data['id_type'];
        $this->economy = $data['economy'];
        return true;
    }

}
