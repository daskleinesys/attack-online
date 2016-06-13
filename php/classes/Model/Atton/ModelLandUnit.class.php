<?php
namespace AttOn\Model\Atton;

use AttOn\Exceptions\NullPointerException;
use AttOn\Model\Atton\Interfaces\ModelUnit;
use AttOn\Model\DataBase\DataSource;
use AttOn\Model\Iterator\ModelIterator;

class ModelLandUnit extends ModelUnit {

    private static $units = array(); // array(id_unit => ModelLandUnit)

    private $killing_sequence;
    private $kill_sequence_offset;
    private $ship_takeover;

    protected function __construct($id, $name, $abbreviation, $price, $speed, $id_type, $killing_sequence, $kill_sequence_offset, $ship_takeover) {
        parent::__construct($id, $name, $abbreviation, $price, $speed, $id_type);
        $this->killing_sequence = $killing_sequence;
        $this->kill_sequence_offset = intval($kill_sequence_offset);
        $this->ship_takeover = intval($ship_takeover);
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
        $query = 'get_land_unit';
        $dict = array(':id_unit' => $id_unit);
        $result = DataSource::getInstance()->epp($query, $dict);
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
            $unit['id_type'],
            $unit['killing_sequence'],
            $unit['kill_sequence_offset'],
            $unit['ship_takeover']
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
        $query = 'get_all_land_units';

        // query units
        $result = DataSource::Singleton()->epp($query);

        foreach ($result as $unit) {
            $id_unit = $unit['id'];
            $models[] = self::getModelById($id_unit);
        }

        return new ModelIterator($models);
    }

    /**
     * @return string
     */
    public function getKillingSequence() {
        return $this->killing_sequence;
    }

    /**
     * @return int
     */
    public function getKillSequenceOffset() {
        return $this->kill_sequence_offset;
    }

    /**
     * @return int
     */
    public function getShipTakeover() {
        return $this->ship_takeover;
    }

}
