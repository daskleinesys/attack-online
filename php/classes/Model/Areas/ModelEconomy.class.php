<?php
namespace Attack\Model\Areas;

use Attack\Database\SQLConnector;
use Attack\Exceptions\DatabaseException;
use Attack\Tools\Iterator\ModelIterator;

class ModelEconomy {

    // member vars
    private $id_resource; // int
    private $res_power; // int

    /**
     * returns an economy model
     *
     * @param $id_resource int
     * @param $res_power int
     */
    private function __construct($id_resource, $res_power) {
        $this->id_resource = intval($id_resource);
        $this->res_power = intval($res_power);
    }

    /**
     * returns an iterator for economy models
     *
     * @param $economy_type string - enum(poor,weak,normal,strong)
     * @param $random_order boolean - set true to get random order
     * @throws DatabaseException
     * @return ModelIterator
     */
    public static function iterator($economy_type, $random_order = true) {
        $models = array();
        $query = 'get_areas_get_resources_by_economy';
        $dict = array();
        $dict[':economy'] = $economy_type;

        // query phases
        $result = SQLConnector::Singleton()->epp($query, $dict);

        foreach ($result as $alloc) {
            $id_res = $alloc['id_resource'];
            $res_pow = $alloc['res_power'];
            $count = $alloc['count'];
            for ($x = 0; $x < $count; $x++) {
                $models[] = new ModelEconomy($id_res, $res_pow);
            }
        }

        if ($random_order) {
            shuffle($models);
        }

        return new ModelIterator($models);
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
    public function getResPower() {
        return $this->res_power;
    }

}
