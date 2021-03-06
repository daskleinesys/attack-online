<?php
namespace Attack\Model\User;

use Attack\Database\SQLConnector;
use Attack\Exceptions\DatabaseException;
use Attack\Tools\Iterator\ModelIterator;
use Attack\Exceptions\NullPointerException;

class ModelColor {

	// phase models
	private static $colors = array(); // array(int id_color => ModelColor)

	// member vars
	private $id; // int
	private $name; // string
	private $key; // string

	/**
	 * returns the color model
     *
	 * @param $id_color int
	 * @throws NullPointerException
	 */
	private function __construct($id_color) {
		$id_color = intval($id_color);
		$this->id = $id_color;

		if (!$this->fill_member_vars()) {
            throw new NullPointerException('Phase not found.');
        }
	}

	/**
	 * returns the specific model
     *
	 * @param $id_color int
	 * @throws NullPointerException
	 * @return ModelColor
	 */
	public static function getModelColor($id_color) {
		if (isset(self::$colors[$id_color])) {
            return self::$colors[$id_color];
        }

		return self::$colors[$id_color] = new ModelColor($id_color);
	}

	/**
	 * returns an iterator for all colors
     *
	 * @throws DatabaseException
	 * @return ModelIterator
	 */
	public static function iterator() {
		$models = array();
		$query = 'get_all_colors';

		// query phases
		try {
			$result = SQLConnector::Singleton()->epp($query);
		} catch (DatabaseException $ex) {
			throw $ex;
		}

		foreach ($result as $color) {
			$id_color = $color['id'];
			$models[] = self::getModelColor($id_color);
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
		$result = SQLConnector::Singleton()->epp('get_color_by_id', array(':id_color' => $this->id));
		if (empty($result)) {
            return false;
        }
		$data = $result[0];

		$this->name = $data['name'];
		$this->key = $data['key'];
		return true;
	}

}
