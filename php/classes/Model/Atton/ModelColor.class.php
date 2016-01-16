<?php
class ModelColor {
	
	// phase models
	private static $colors = array(); // array(int id_color => ModelColor)
	
	// member vars
	private $id; // int
	private $name; // string
	private $color; // string
	
	/**
	 * returns the color model
	 * @param $id_color int
	 * @throws NullPointerException
	 * @return ModelColor
	 */
	private function __construct($id_color) {
		$id_color = intval($id_color);
		$this->id = $id_color;

		if (!$this->fill_member_vars()) throw new NullPointerException('Phase not found.');
	}
	
	/**
	 * returns the specific model
	 * @param $id_color int
	 * @throws NullPointerException
	 * @return ModelColor
	 */
	public static function getModelColor($id_color) {
		if (isset(self::$colors[$id_color])) return self::$colors[$id_color];
		
		return self::$colors[$id_color] = new ModelColor($id_color);
	}
	
	/**
	 * returns an iterator for all colors
	 * @throws DataSourceException
	 * @return ModelIterator
	 */
	public static function iterator() {
		$models = array();
		$query = 'get_all_colors';
		
		// query phases
		try {
			$result = DataSource::Singleton()->epp($query);
		} catch (DataSourceException $ex) {
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
	public function getColor() {
		return $this->color;
	}
	
	private function fill_member_vars() {
		// check if there is a game
		$result = DataSource::Singleton()->epp('get_color',array(':id_color' => $this->id));
		if (empty($result)) return false;
		$data = $result[0];
		
		$this->name = $data['name'];
		$this->color = $data['color'];
		return true;
	}
}


?>