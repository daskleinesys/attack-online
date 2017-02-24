<?php
namespace Attack\Model\Units;

use Attack\Exceptions\NullPointerException;
use Attack\Model\Units\Interfaces\ModelUnit;
use Attack\Database\SQLConnector;
use Attack\Tools\Iterator\ModelIterator;

class ModelLandUnit extends ModelUnit {

    private static $units = array(); // array(id_unit => ModelLandUnit)

    protected function __construct($id, $name, $abbreviation, $price, $speed, $id_type) {
        parent::__construct($id, $name, $abbreviation, $price, $speed, $id_type);
    }

    /**
     * returns the model to the selected land unit
     *
     * @param $id_unit int
     * @throws NullPointerException
     * @return ModelLandUnit
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
            throw new NullPointerException('Unit not found.');
        }
        $unit = $result[0];
        self::$units[$id_unit] = new ModelLandUnit(
            $unit['id'],
            $unit['name'],
            $unit['abbreviation'],
            $unit['price'],
            $unit['speed'],
            $unit['id_type']
        );
        return self::$units[$id_unit];
    }

    /**
     * returns iterator for all land units
     *
     * @return ModelIterator
     */
    public static function iterator() {
        $models = array();
        $query = 'get_units_by_type';

        // query land units
        $dict = array(
            ':id_type' => TYPE_LAND
        );
        $result = SQLConnector::Singleton()->epp($query, $dict);
        foreach ($result as $unit) {
            $id_unit = $unit['id'];
            $models[] = self::getModelById($id_unit);
        }
        // query air units
        $dict = array(
            ':id_type' => TYPE_AIR
        );
        $result = SQLConnector::Singleton()->epp($query, $dict);
        foreach ($result as $unit) {
            $id_unit = $unit['id'];
            $models[] = self::getModelById($id_unit);
        }

        return new ModelIterator($models);
    }

}
