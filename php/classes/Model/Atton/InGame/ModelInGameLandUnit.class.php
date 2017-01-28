<?php
namespace Attack\Model\Atton\InGame;

use Attack\Model\Atton\Interfaces\ModelInGameUnit;
use Attack\Model\Atton\ModelLandUnit;
use Attack\Database\SQLConnector;
use Attack\Database\SQLCommands;

class ModelInGameLandUnit extends ModelInGameUnit {

    private static $units = array(); // array(int id_game => array(int id_zarea => array(int id_user => array(int id_zunit => ModelInGameLandUnit))))

    private $count;

    /**
     * creates the model
     *
     * @param $id int
     * @param $id_unit int
     * @param $id_user int
     * @param $id_zarea int
     * @param $count int
     * @param $id_game int
     * @return ModelInGameLandUnit
     */
    protected function __construct($id, $id_unit, $id_user, $id_zarea, $count, $id_game) {
        parent::__construct($id, $id_user, $id_game, $id_unit, $id_zarea);
        $this->count = intval($count);
    }

    /**
     * returns the model for the given data, if not existing: creates it
     *
     * @param $id_game int
     * @param $id_zarea int
     * @param $id_user int
     * @param $id_unit int
     * @return ModelInGameLandUnit
     */
    public static function getModelByIdZAreaUserUnit($id_game, $id_zarea, $id_user, $id_unit) {
        $id_game = intval($id_game);
        $id_zarea = intval($id_zarea);
        $id_user = intval($id_user);
        $id_unit = intval($id_unit);

        // check if model is already set
        if (isset(self::$units[$id_game][$id_zarea][$id_user][$id_unit])) {
            return self::$units[$id_game][$id_zarea][$id_user][$id_unit];
        }

        // if model not already set, load from database or create it
        SQLCommands::init($id_game);
        $query = 'get_land_units_for_zarea_user_unit';
        $dict = array();
        $dict[':id_zarea'] = $id_zarea;
        $dict[':id_user'] = $id_user;
        $dict[':id_unit'] = $id_unit;
        $result = SQLConnector::getInstance()->epp($query, $dict);
        if (empty($result)) {
            self::createModel($id_game, $id_zarea, $id_user, $id_unit);
        } else {
            $unit = $result[0];
            self::$units[$id_game][$id_zarea][$id_user][$id_unit] = new ModelInGameLandUnit($unit['id'], $unit['id_unit'], $unit['id_user'], $unit['id_zarea'], $unit['count'], $id_game);
        }
        return self::$units[$id_game][$id_zarea][$id_user][$id_unit];
    }

    /**
     * returns all models for the given area/user
     *
     * @param $id_game int
     * @param $id_zarea int
     * @param $id_user int
     * @return array - array(int id_unit => ModelInGameLandUnit)
     */
    public static function getUnitsByIdZAreaUser($id_game, $id_zarea, $id_user) {
        $output = array();
        $iter = ModelLandUnit::iterator();
        while ($iter->hasNext()) {
            $landUnit = $iter->next();
            $id_unit = (int)$landUnit->getId();
            $output[$id_unit] = self::getModelByIdZAreaUserUnit($id_game, $id_zarea, $id_user, $id_unit);
        }
        return $output;
    }

    /**
     * @return int
     */
    public function getCount() {
        return $this->count;
    }

    /**
     * sets the unit count for this area to the given numer
     * should always be greater or equal 0
     *
     * @param $count int
     * @return void
     */
    public function setCount($count) {
        $count = intval($count);
        if ($count < 0) {
            $count = 0;
        }
        SQLCommands::init($this->id_game);
        $query = 'set_land_unit_count';
        $dict = array(':id_zunit' => $this->id, ':count' => $count);
        SQLConnector::getInstance()->epp($query, $dict);
        $this->count = $count;
    }

    /**
     * adds the given integer to the number of units, can be negative, but it must not drop the count below 0
     *
     * @param $count int
     * @return void
     */
    public function addCount($count) {
        $newCount = $this->count + intval($count);
        if ($newCount < 0) {
            $newCount = 0;
        }
        $this->setCount($newCount);
    }

    private static function createModel($id_game, $id_zarea, $id_user, $id_unit) {
        SQLCommands::init($id_game);
        $query = 'create_unit_for_zarea_user';
        $dict = array();
        $dict[':id_user'] = $id_user;
        $dict[':id_zarea'] = $id_zarea;
        $dict[':id_unit'] = $id_unit;
        $dict[':count'] = 0;
        SQLConnector::getInstance()->epp($query, $dict);
        $id = SQLConnector::getInstance()->getLastInsertId();

        self::$units[$id_game][$id_zarea][$id_user][$id_unit] = new ModelInGameLandUnit($id, $id_unit, $id_user, $id_zarea, 0, $id_game);
    }

}
