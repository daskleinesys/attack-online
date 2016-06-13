<?php
namespace AttOn\Model\Atton;

use AttOn\Model\DataBase\DataSource;
use AttOn\Model\Iterator\ModelIterator;
use AttOn\Exceptions\NullPointerException;

class ModelGameMode {

	// phase models
	private static $game_modes = array(); // array(int id_game_mode => ModelGameMode)

	// member vars
	private $id; // int
	private $name; // string
	private $abbreviation; // string
	private $description; // string
	private $phases; // array

	/**
	 * returns the phase model
     *
	 * @param $id_game_mode int
	 * @throws NullPointerException
	 * @return ModelGameMode
	 */
	private function __construct($id_game_mode) {
		$id_game_mode = intval($id_game_mode);
		$this->id = $id_game_mode;

		if (!$this->fill_member_vars()) {
            throw new NullPointerException('GameMode not found.');
        }
	}

	/**
	 * returns the specific model
     *
	 * @param $id_game_mode int
	 * @throws NullPointerException
	 * @return ModelGameMode
	 */
	public static function getGameMode($id_game_mode) {
		if (isset(self::$game_modes[$id_game_mode])) {
            return self::$game_modes[$id_game_mode];
        }

		return self::$game_modes[$id_game_mode] = new ModelGameMode($id_game_mode);
	}

	/**
	 * returns an iterator for all game modes
     *
	 * @throws DataSourceException
	 * @return ModelIterator
	 */
	public static function iterator() {
		$models = array();
		$query = 'get_all_game_modes';

		// query phases
		try {
			$result = DataSource::Singleton()->epp($query);
		} catch (DataSourceException $ex) {
			throw $ex;
		}

		foreach ($result as $game_mode) {
			$id_game_mode = $game_mode['id'];
			$models[] = self::getGameMode($id_game_mode);
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
	public function getAbbreviation() {
		return $this->abbreviation;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @return array(int id_phase)
	 */
	public function getPhases() {
		return $this->phases;
	}

	private function fill_member_vars() {
		// check if there is a game
		$result = DataSource::Singleton()->epp('get_game_mode', array(':id_game_mode' => $this->id));
		if (empty($result)) {
            return false;
        }
		$data = $result[0];

		$this->name = $data['name'];
		$this->abbreviation = $data['abbreviation'];
		$this->description = $data['description'];
		$this->phases = explode(VARIABLES_SPLITTER, $data['phases']);
		return true;
	}

}
