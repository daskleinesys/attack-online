<?php
namespace Attack\Model\Atton;

use Attack\Database\SQLConnector;
use Attack\Exceptions\NullPointerException;

class ModelOptionType {

    // phase models
    private static $types = array(); // array(int it_type => ModelOptionType)

    // member vars
    private $id; // int
    private $units; // int
    private $countries; // int

    /**
     * returns the option type model
     *
     * @param $id_phase int
     * @param $units int
     * @param $countries int
     * @return ModelOptionType
     */
    private function __construct($id, $units, $countries) {
        $this->id = intval($id);
        $this->units = intval($units);
        $this->countries = intval($countries);
    }

    /**
     * returns the specific model
     *
     * @param $id_option_type int
     * @throws NullPointerException
     * @return ModelOptionType
     */
    public static function getOptionType($id_option_type) {
        if (isset(self::$types[$id_option_type])) {
            return self::$types[$id_option_type];
        }

        self::load_option_types();

        if (!isset(self::$types[$id_option_type])) {
            throw new NullPointerException('Optiontype not found.');
        }
        return self::$types[$id_option_type];
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getUnits() {
        return $this->units;
    }

    /**
     * @return int
     */
    public function getCountries() {
        return $this->countries;
    }

    private static function load_option_types() {
        $query = 'get_option_types';
        $result = SQLConnector::getInstance()->epp($query);
        foreach ($result as $type) {
            self::$types[$type['id']] = new ModelOptionType($type['id'], $type['units'], $type['countries']);
        }
    }

}
