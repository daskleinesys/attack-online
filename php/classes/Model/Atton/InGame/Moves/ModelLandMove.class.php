<?php

class ModelLandMove extends ModelMove {
	private static $moves = array(); // array(int id_game => array(int id_move => ModelLandMove))
	
	private $steps = array(); // array(int step_nr => int id_zarea)
	private $units = array(); // array(int id_unit => count)
	
	/**
	 * creates the model
	 * @param $id_game int
	 * @param $id_move int
	 * @param $id_user int
	 * @param $id_phase int
	 * @param $round int
	 * @param $deleted boolean
	 * @param $steps array(int step_nr => int id_zarea)
	 * @param $units array(int id_unit => count)
	 * @return ModelLandMove
	 */
	protected function __construct($id_game,$id_move,$id_user,$id_phase,$round,$deleted,$steps,$units) {
		parent::__construct($id_game,$id_move,$id_user,$id_phase,$round,$deleted);
		$this->steps = $steps;
		$this->units = $units;
	}
	
	/**
	 * returns the corresponding model
	 * @param $id_game int
	 * @param $id_move int
	 * @throws NullPointerException. ModelException
	 * @return ModelLandMove
	 */
	public static function getLandMove($id_game,$id_move) {
		if (isset(self::$moves[$id_game][$id_move])) return self::$moves[$id_game][$id_move];
		
		SQLCommands::init(intval($id_game));
		$query = 'get_land_move';
		$dict = array();
		$dict [':id_move'] = intval($id_move);
		$dict [':id_phase'] = PHASE_LANDMOVE;
		$result = DataSource::getInstance()->epp($query,$dict);
		if (empty($result)) throw new NullPointerException('Move not found');
		$steps = array();
		$units = array();
		foreach ($result as $line) {
			if ($line['step'] != null && $line['id_zarea'] != null) {
				$steps[$line['step']] = $line['id_zarea'];
			}
			if ($line['id_unit'] != null && $line['numberof'] != null) {
				$units[$line['id_unit']] = $line['numberof'];
			}
		}
		return self::$moves[$id_game][$id_move] = new ModelLandMove($id_game,$id_move,$result[0]['id_user'],PHASE_LANDMOVE,$result[0]['round'],$result[0]['deleted'],$steps,$units);
	}
	
	/**
	 * returns an iterator for landmoves, specify round and/or user if necessary
	 * @param $id_game int
	 * @param $round int
	 * @param $id_user int
	 * @return ModelIterator
	 */
	public static function iterator($id_game,$round = null,$id_user = null) {
		SQLCommands::init(intval($id_game));
		$query = 'get_specific_moves';
		$dict = array();
		$dict[':id_phase'] = PHASE_LANDMOVE;
		$dict[':id_user'] = ($id_user == null) ? '%' : intval($id_user);
		$dict[':round'] = ($round == null) ? '%' : intval($round);
		
		$result = DataSource::Singleton()->epp($query,$dict);
		$moves = array();
		foreach ($result as $move) {
			$moves[] = self::getLandMove($id_game, $move['id']);
		}
		
		return new ModelIterator($moves);
	}
	
	/**
	 * creates land move for user
	 * @param $id_game int
	 * @param $id_move int
	 * @param $id_user int
	 * @param $id_phase int
	 * @param $round int
	 * @param $steps array(int step_nr => int id_zarea) -> step_nr counting from 1 to x
	 * @param $units array(int id_unit => count)
	 * @throws NullPointerException, ModelException
	 * @return ModelLandMove
	 */
	public static function createLandMove($id_game,$id_user,$round,$steps,$units) {
		SQLCommands::init(intval($id_game));
		
		// CREATE MOVE
		$query = 'create_move';
		$dict = array();
		$dict [':id_user'] = intval($id_user);
		$dict [':id_phase'] = PHASE_LANDMOVE;
		$dict [':round'] = $round;
		DataSource::Singleton()->epp($query,$dict);
		$id_move = DataSource::getInstance()->getLastInsertId();
		
		try {
			// INSERT MOVE STEPS
			$x = 0;
			foreach ($steps as $step => $id_zarea) {
				$x++;
				ModelGameArea::getGameArea($id_game, $id_zarea);
				if (!isset($steps[$x])) {
					throw new ModelException('Cannot create landmove, steps not consistent.');
				}
				$query = 'create_step_for_move';
				$dict = array();
				$dict [':id_move'] = intval($id_move);
				$dict [':step'] = intval($step);
				DataSource::Singleton()->epp($query,$dict);
				$id_step = DataSource::getInstance()->getLastInsertId();
				$query = 'insert_move_area_for_step';
				$dict = array();
				$dict [':id_step'] = intval($id_step);
				$dict [':id_zarea'] = intval($id_zarea);
				DataSource::Singleton()->epp($query,$dict);
			}
			// INSERT UNITS
			foreach ($units as $id_unit => $count) {
				ModelLandUnit::getModelById($id_unit);
				$query = 'insert_land_units_for_move';
				$dict = array();
				$dict [':id_unit'] = intval($id_unit);
				$dict [':id_move'] = intval($id_move);
				$dict [':count'] = intval($count);
				DataSource::Singleton()->epp($query,$dict);
			}
		} catch (Exception $ex) {
			self::deleteMove($id_game, $id_move);
			throw $ex;
		}
		
		return self::$moves[$id_game][$id_move] = new ModelLandMove($id_game,$id_move,$id_user,PHASE_LANDMOVE,$round,false,$steps,$units);
	}
	
	/**
	 * deletes this move
	 * @param $id_game
	 * @param $id_move
	 * @throws NullPointerException,ModelException
	 * @return void
	 */
	public static function deleteMove($id_game,$id_move) {
		$_Move = ModelLandMove::getLandMove($id_game, $id_move);
		$_Game = ModelGame::getGame($id_game);
		if ($_Game->getRound() > $_Move->getRound()) throw new ModelException('Cannot delete move as it is not from the active round.');
		if ($_Game->getRound() == $_Move->getRound() && $_Game->getIdPhase() > PHASE_LANDMOVE) throw new ModelException('Cannot delete move as it is already over.');
		
		$dict = array();
		$dict [':id_move'] = intval($id_move);
		// DELETE AREAS FOR MOVE
		$query = 'delete_move_areas_for_move';
		DataSource::Singleton()->epp($query,$dict);
		// DELETE STEPS FOR MOVE
		$query = 'delete_steps_for_move';
		DataSource::Singleton()->epp($query,$dict);
		// DELETE UNITS FOR MOVE
		$query = 'delete_units_for_move';
		DataSource::Singleton()->epp($query,$dict);
		// DELETE MOVE
		$query = 'delete_move';
		DataSource::Singleton()->epp($query,$dict);
	}
	
	/**
	 * @return array(int step_nr => int id_zarea)
	 */
	public function getSteps() {
		return $this->steps;
	}
	
	/**
	 * @return array(int id_unit => count)
	 */
	public function getUnits() {
		return $this->units;
	}
}

?>