<?php
namespace Attack\Model\Units;

use Attack\Exceptions\NullPointerException;
use Attack\Model\Units\Interfaces\ModelUnit;
use Attack\Database\SQLConnector;
use Attack\Tools\Iterator\ModelIterator;

class ModelShip extends ModelUnit {

    private static $units = array(); // array(id_unit => ModelLandUnit)

    private $tanksize;
    private $hitpoints;

    protected function __construct($id, $name, $abbreviation, $price, $speed, $id_type, $tanksize, $hitpoints) {
        parent::__construct($id, $name, $abbreviation, $price, $speed, $id_type);
        $this->tanksize = intval($tanksize);
        $this->hitpoints = intval($hitpoints);
    }

    /**
     * returns the model to the selected ship
     *
     * @param $id_unit int
     * @return ModelShip
     * @throws NullPointerException
     */
    public static function getModelById($id_unit) {
        $id_unit = intval($id_unit);
        if (isset(self::$units[$id_unit])) {
            return self::$units[$id_unit];
        }
        $query = 'get_unit_by_id';
        $dict = array(':id_unit' => $id_unit);
        $result = SQLConnector::getInstance()->epp($query, $dict);
        if (empty($result)) {
            throw new NullPointerException("Ship with id $id_unit not found.");
        }
        $unit = $result[0];
        return self::$units[$id_unit] = new ModelShip(
            $unit['id'],
            $unit['name'],
            $unit['abbreviation'],
            $unit['price'],
            $unit['speed'],
            $unit['id_type'],
            $unit['tanksize'],
            $unit['hitpoints']
        );
    }

    /**
     * returns iterator for all ships
     *
     * @return ModelIterator
     */
    public static function iterator() {
        $models = array();
        $query = 'get_units_by_type';
        $dict = array(
            ':id_type' => TYPE_SEA
        );

        // query units
        $result = SQLConnector::Singleton()->epp($query, $dict);

        foreach ($result as $unit) {
            $id_unit = $unit['id'];
            $models[] = self::getModelById($id_unit);
        }

        return new ModelIterator($models);
    }

    /**
     * @return int
     */
    public function getTanksize() {
        return $this->tanksize;
    }

    /**
     * @return int
     */
    public function getHitpoints() {
        return $this->hitpoints;
    }
}
