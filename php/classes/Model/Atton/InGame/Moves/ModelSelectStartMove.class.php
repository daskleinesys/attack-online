<?php

class ModelSelectStartMove extends ModelMove {
	private static $moves = array(); // array(int id_game => array(int id_move => ModelSelectStartMove))
	
	private $regions = array(); // array(int option_number => array(int id_zarea))
	
	/**
	 * creates the model
	 * @param $id_game int
	 * @param $id_move int
	 * @param $id_user int
	 * @param $id_phase int
	 * @param $round int
	 * @param $deleted boolean
	 * @param $regions array
	 * @return ModelSelectStartMove
	 */
	protected function __construct($id_game,$id_move,$id_user,$id_phase,$round,$deleted,$regions) {
		parent::__construct($id_game,$id_move,$id_user,$id_phase,$round,$deleted);
		$this->regions = $regions;
	}
	
	/**
	 * returns the corresponding model
	 * @param $id_game int
	 * @param $id_move int
	 * @throws NullPointerException. ModelException
	 * @return ModelSelectStartMove
	 */
	public static function getSelectStartMove($id_game,$id_move) {
		if (isset(self::$moves[$id_game][$id_move])) return self::$moves[$id_game][$id_move];
		
		SQLCommands::init(intval($id_game));
		$query = 'get_start_move';
		$dict = array();
		$dict [':id_move'] = intval($id_move);
		$dict [':id_phase'] = PHASE_SELECTSTART;
		$dict [':round'] = 0;
		$result = DataSource::getInstance()->epp($query,$dict);
		if (empty($result)) throw new NullPointerException('Move not found');
		$regions = array();
		foreach ($result as $line) {
			if ($line['step'] == null) continue;
			if ($line['id_zarea'] == null) continue;
			if (!isset($regions[$line['step']])) $regions[$line['step']] = array();
			$regions[$line['step']][] = $line['id_zarea'];
		}
		return self::$moves[$id_game][$id_move] = new ModelSelectStartMove($id_game,$id_move,$result[0]['id_user'],$result[0]['deleted'],PHASE_SELECTSTART,0,$regions);
	}
	
	/**
	 * returns the corresponding model -> creates it if necessary
	 * @param $id_game int
	 * @param $id_user int
	 * @throws NullPointerException
	 * @return ModelSelectStartMove
	 */
	public static function getSelectStartMoveForUser($id_game,$id_user) {
		SQLCommands::init(intval($id_game));
		$query = 'get_start_move_for_user';
		$dict = array();
		$dict [':id_user'] = intval($id_user);
		$dict [':id_phase'] = PHASE_SELECTSTART;
		$dict [':round'] = 0;
		$result = DataSource::getInstance()->epp($query,$dict);
		Modeluser::getUser($id_user);
		if (empty($result)) $id_move = self::createSelectStartMove($id_game,$id_user);
		else $id_move = $result[0]['id'];
		
		return self::getSelectStartMove($id_game, $id_move);
	}
	
	/**
	 * returns an iterator for all select-start moves in this game
	 * @return ModelIterator
	 */
	public static function iterator($id_game) {
		SQLCommands::init(intval($id_game));
		$query = 'get_all_moves_for_phase_and_round';
		$dict = array();
		$dict [':id_phase'] = PHASE_SELECTSTART;
		$dict [':round'] = 0;
		
		$result = DataSource::Singleton()->epp($query,$dict);
		$moves = array();
		foreach ($result as $move) {
			$id_move = $move['id'];
			$moves[] = self::getSelectStartMove($id_game, $id_move);
		}
		
		return new ModelIterator($moves);
	}
	
	/**
	 * creates or updates the start move for user
	 * @param $id_game int
	 * @param $id_user int
	 * @return $id_move int
	 */
	private static function createSelectStartMove($id_game,$id_user) {
		SQLCommands::init(intval($id_game));
		$query = 'create_move';
		$dict = array();
		$dict [':id_user'] = intval($id_user);
		$dict [':id_phase'] = PHASE_SELECTSTART;
		$dict [':round'] = 0;
		DataSource::Singleton()->epp($query,$dict);
		$id_move = DataSource::getInstance()->getLastInsertId();
		return $id_move;
	}
	
	/**
	 * updates selected areas if necessary
	 * @param $option_number int
	 * @param $areas array(int id_zarea)
	 * @return void
	 */
	public function setRegions($option_number,$zareas) {
		$option_number = intval($option_number);
		// check if areas are already set
		if (isset($this->regions[$option_number])) {
			$found_all = true;
			foreach ($zareas as $id_zarea) {
				if (!in_array($id_zarea, $this->regions[$option_number])) $found_all = false;
			}
			if ($found_all) return;
		}
		
		$dict = array();
		$dict [':id_move'] = $this->id;
		$dict [':step'] = $option_number;
		
		if (isset($this->regions[$option_number])) {
			unset($this->regions[$option_number]);
			$query = 'delete_move_areas_for_step';
			DataSource::Singleton()->epp($query,$dict);
		} else {
			$query = 'create_step_for_move';
			DataSource::Singleton()->epp($query,$dict);
		}
		
		// insert areas
		$query = 'get_id_for_step_and_move';
		$result = DataSource::getInstance()->epp($query,$dict);
		$id_route = $result[0]['id'];
		
		$query = 'insert_move_area_for_step';
		$dict = array();
		$dict [':id_step'] = $id_route;
		foreach ($zareas as $id_zarea) {
			$dict[':id_zarea'] = $id_zarea;
			DataSource::getInstance()->epp($query,$dict);
		}
		$this->regions[$option_number] = $zareas;
	}

	/**
	 * return true if country is already selected
	 */
	public function checkIfAreaIsSelected($option_number,$id_zarea) {
		if (!isset($this->regions[$option_number])) return false;
		if (in_array($id_zarea, $this->regions[$option_number])) return true;
		return false;
	}
	
	/**
	 * @return array(int option_number => array(int id_zarea))
	 */
	public function getRegions() {
		return $this->regions;
	}
	
	/**
	 * returns the id of the user of this move
	 * @return int id_user
	 */
	public function getIdUser() {
		return $this->id_user;
	}
}

?>