<?php
namespace Attack\Model\Game;

use Attack\Database\SQLConnector;
use Attack\Exceptions\DatabaseException;
use Attack\Tools\Iterator\ModelIterator;
use Attack\Exceptions\NullPointerException;

class ModelPhase implements \JsonSerializable {

    // phase models
    private static $phases = array(); // array(int id_phase => ModelPhase)

    // member vars
    private $id; // int
    private $name; // string
    private $key; // string

    /**
     * returns the phase model
     *
     * @param $id_phase int
     * @throws NullPointerException
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
     * @throws DatabaseException
     * @return ModelIterator
     */
    public static function iterator() {
        $models = array();
        $query = 'get_all_phases';

        // query phases
        try {
            $result = SQLConnector::Singleton()->epp($query);
        } catch (DatabaseException $ex) {
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
    public function getKey() {
        return $this->key;
    }

    private function fill_member_vars() {
        // check if there is a game
        $result = SQLConnector::Singleton()->epp('get_phase_by_id', array(':id_phase' => $this->id));
        if (empty($result)) {
            return false;
        }
        $data = $result[0];

        $this->name = $data['name'];
        $this->key = $data['key'];
        return true;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'key' => $this->key,
        ];
    }
}
