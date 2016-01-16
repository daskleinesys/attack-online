<?php
// TODO: ALLES!
class ModelStartingSet {
	
	// phase models
	private static $sets = array(); // array(int id_set => ModelStartingSet)
	
	// member vars
	private $id; // int
	private $name; // string
	private $players; // int
	
	/**
	 * returns the starting-set model
	 * @param $id_set int
	 * @throws NullPointerException
	 * @return ModelStartingSet
	 */
	private function __construct($id_set) {
		$id_set = intval($id_set);
		$this->id = $id_set;

		if (!$this->fill_member_vars()) throw new NullPointerException('Starting Set not found.');
	}
	
	/**
	 * returns the specific model
	 * @param $id_color int
	 * @throws NullPointerException
	 * @return ModelStartingSet
	 */
	public static function getSet($id_set) {
		if (isset(self::$sets[$id_set])) return self::$sets[$id_set];
		
		return self::$sets[$id_set] = new ModelStartingSet($id_set);
	}
	
	/**
	 * returns an iterator for all colors
	 * @throws DataSourceException,NullPointerException
	 * @return ModelIterator
	 */
	public static function iterator($players = null,$random_order = false) {
		$models = array();
		$query = 'get_starting_sets';
		$dict = array();
		$dict[':players'] = ($players == null) ? '%' : intval($players);
		
		// query phases
		try {
			$result = DataSource::Singleton()->epp($query,$dict);
		} catch (DataSourceException $ex) {
			throw $ex;
		}
		
		if (empty($result)) throw new NullPointerException('This number of players is not supported.');
		
		foreach ($result as $set) {
			$id_set = $set['id'];
			$models[] = self::getSet($id_set);
		}
		
		if ($random_order) shuffle($models);
		
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
	public function getPlayers() {
		return $this->players;
	}
	
	private function fill_member_vars() {
		// check if there is a game
		$result = DataSource::Singleton()->epp('get_starting_set',array(':id_set' => $this->id));
		if (empty($result)) return false;
		$data = $result[0];
		
		$this->name = $data['name'];
		$this->players = $data['players'];
		return true;
	}
}


?>