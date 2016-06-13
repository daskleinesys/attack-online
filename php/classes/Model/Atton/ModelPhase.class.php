<?php
namespace AttOn\Model\Atton;

use AttOn\Model\DataBase\DataSource;
use AttOn\Model\Iterator\ModelIterator;
use AttOn\Exceptions\NullPointerException;

class ModelPhase {

    // phase models
    private static $phases = array(); // array(int id_phase => ModelPhase)

    // member vars
    private $id; // int
    private $name; // string
    private $label; // string
    private $id_type; // int

    /**
     * returns the phase model
     *
     * @param $id_phase int
     * @throws NullPointerException
     * @return ModelPhase
     */
    private function __construct($id_phase) {
        $this->id = $id_phase;

        if (!$this->fill_member_vars()) {
            throw new NullPointerException('Phase not found.');
        }
    }

    /**
     * returns the specific model
     *
     * @param $id_phase int
     * @throws NullPointerException
     * @return ModelPhase
     */
    public static function getPhase($id_phase) {
        if (isset(self::$phases[$id_phase])) {
            return self::$phases[$id_phase];
        }

        return self::$phases[$id_phase] = new ModelPhase($id_phase);
    }

    /**
     * returns an iterator for all phases
     *
     * @throws DataSourceException
     * @return ModelIterator
     */
    public static function iterator() {
        $models = array();
        $query = 'get_all_phases';

        // query phases
        try {
            $result = DataSource::Singleton()->epp($query);
        } catch (DataSourceException $ex) {
            throw $ex;
        }

        foreach ($result as $phase) {
            $id_phase = $phase['id'];
            $models[] = self::getPhase($id_phase);
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
     * @return string
     */
    public function getLabel() {
        return $this->label;
    }

    /**
     * @return int
     */
    public function getIdType() {
        return $this->id_type;
    }

    private function fill_member_vars() {
        // check if there is a game
        $result = DataSource::Singleton()->epp('get_phase_info', array(':id_phase' => $this->id));
        if (empty($result)) {
            return false;
        }
        $data = $result[0];

        $this->name = $data['name'];
        $this->label = $data['label'];
        $this->id_type = $data['id_type'];
        return true;
    }

}
