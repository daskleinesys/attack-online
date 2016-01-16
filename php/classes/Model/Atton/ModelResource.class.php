<?php

class ModelResource {
	
	// phase models
	private static $resources = array(); // array(int id_resource => ModelResource)
	
	// member vars
	private $id; // int
	private $name; // string
	private $label; // string
	private $id_type; // int
	
	/**
	 * returns the resource model
	 * @param $id_resource int
	 * @throws NullPointerException
	 * @return ModelResource
	 */
	private function __construct($id_resource) {
		$id_resource = intval($id_resource);
		$this->id = $id_resource;

		if (!$this->fill_member_vars()) throw new NullPointerException('Resource not found.');
	}
	
	/**
	 * returns the specific model
	 * @param $id_resource int
	 * @throws NullPointerException
	 * @return ModelArea
	 */
	public static function getResource($id_resource) {
		if (isset(self::$resources[$id_resource])) return self::$resources[$id_resource];
		
		return self::$resources[$id_resource] = new ModelResource($id_resource);
	}
	
	/**
	 * returns an iterator for resources
	 * @throws DataSourceException
	 * @return ModelIterator
	 */
	public static function iterator() {
		$models = array();
		$query = 'get_resources';
		
		// query phases
		try {
			$result = DataSource::Singleton()->epp($query);
		} catch (DataSourceException $ex) {
			throw $ex;
		}
		
		foreach ($result as $res) {
			$id_resource = $res['id'];
			$models[] = self::getResource($id_resource);
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
		$result = DataSource::Singleton()->epp('get_resource_info',array(':id_resource' => $this->id));
		if (empty($result)) return false;
		$data = $result[0];
		
		$this->name = $data['name'];
		$this->label = $data['label'];
		$this->id_type = $data['id_type'];
		return true;
	}
}

?>