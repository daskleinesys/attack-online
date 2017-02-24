<?php
namespace Attack\Model\Game\Start;

use Attack\Database\SQLConnector;
use Attack\Exceptions\NullPointerException;

class ModelStartRegion {

    /**
     * @var array(
     *     int id_set => array(
     *         int id_optiontype => array(
     *             int option_group => array(int id_area => ModelStartRegion)
     *         )
     *     )
     * )
     */
    private static $start_regions = array();

    // member vars
    private $id; // int
    private $id_area; // int
    private $id_set; // int
    private $id_optiontype; // int
    private $option_group; // int

    /**
     * returns the start region model
     *
     * @param $id int
     * @param $id_area int
     * @param $id_optiontype int
     * @param $id_set int
     * @param $option_number int
     */
    private function __construct($id, $id_area, $id_optiontype, $id_set, $option_number) {
        $this->id = intval($id);
        $this->id_area = intval($id_area);
        $this->id_optiontype = intval($id_optiontype);
        $this->id_set = intval($id_set);
        $this->option_group = intval($option_number);
    }

    /**
     * returns all startregions for the given set
     *
     * @param $id_set int
     * @throws NullPointerException
     * @return array(int id_opttype => array(int option_number => array(int id_area => ModelStartRegion)))
     */
    public static function getRegionsForSet($id_set) {
        $id_set = intval($id_set);
        if (isset(self::$start_regions[$id_set])) {
            return self::$start_regions[$id_set];
        }

        $query = 'get_start_set_areas_by_set';
        $dict[':id_set'] = $id_set;
        $result = SQLConnector::getInstance()->epp($query, $dict);
        if (empty($result)) {
            throw new NullPointerException('Set not found.');
        }
        foreach ($result as $start_region) {
            $id_optiontype = $start_region['id_optiontype'];
            $option_group = $start_region['option_group'];
            $id_area = $start_region['id_area'];
            self::$start_regions[$id_set][$id_optiontype][$option_group][$id_area] = new ModelStartRegion(
                $start_region['id'],
                $id_area,
                $id_optiontype,
                $id_set,
                $option_group
            );
        }
        return self::$start_regions[$id_set];
    }

    /**
     * returns all startregions for option_number in this set
     *
     * @param $id_set int
     * @param $option_number int
     * @throws NullPointerException
     * @return array(int id_area => ModelStartRegion)
     */
    public static function getRegionsForSetAndOption($id_set, $option_number) {
        $id_set = intval($id_set);
        $option_number = intval($option_number);
        if (!isset(self::$start_regions[$id_set])) {
            self::getRegionsForSet($id_set);
        }
        foreach (self::$start_regions[$id_set] as $options_types) {
            if (isset($options_types[$option_number])) {
                return $options_types[$option_number];
            }
        }
        throw new NullPointerException('Option Number for set not found.');
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
    public function getIdArea() {
        return $this->id_area;
    }

    /**
     * @return int
     */
    public function getIdOptionType() {
        return $this->id_optiontype;
    }

    /**
     * @return int
     */
    public function getIdSet() {
        return $this->id_set;
    }

    /**
     * @return int
     */
    public function getOptionGroup() {
        return $this->option_group;
    }

}
