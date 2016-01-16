<?php
class LogicSelectStart extends PhaseLogic {
	private $_Logger;
	
	/**
	 * returns object to run game logic -> should only be called by factory
	 * @param $id_game int
	 * @return LogicSelectStart
	 */
	public function __construct($id_game) {
		parent::__construct($id_game, PHASE_SELECTSTART);
		$this->_Logger = Logger::getLogger('LogicSelectStart');
	}
	
	/**
	 * run the game logic
	 * @return void
	 */
	public function run() {
		if (!$this->checkIfValid()) throw new LogicException('Game '.$this->id_game.' not valid for processing.');
		$this->startProcessing();
		
		try {
			
			// run through moves for each user
			$iter = ModelSelectStartMove::iterator($this->id_game);
			while ($iter->hasNext()) {
				// areas to select for user
				$areas_selected = array();
				$_Move = $iter->next();
				$regions_selected = $_Move->getRegions(); // array(int option_number => array(int id_zarea))
				$id_user = $_Move->getIdUser();
				$id_set = ModelIsInGameInfo::getIsInGameInfo($id_user, $this->id_game)->getIdStartingSet();
				
				foreach ($regions_selected as $option_number => $areas) {
					$regions = ModelStartRegion::getRegionsForSetAndOption($id_set, $option_number); // array(int id_area => ModelStartRegion)
					
					foreach ($areas as $id_zarea) {
						$_ModelGameArea = ModelGameArea::getGameArea($this->id_game, $id_zarea);
						$id_area = $_ModelGameArea->getIdArea();
						$unit_count = ModelOptionType::getOptionType($regions[$id_area]->getIdOptionType())->getUnits();
						
						// set user for game area
						$_ModelGameArea->setIdUser($id_user);
						
						// create units
						$iterUnits = ModelLandUnit::iterator();
						while ($iterUnits->hasNext()) {
							$_LandUnit = $iterUnits->next();
							$id_unit = $_LandUnit->getId();
							$_InGameLandUnit = ModelInGameLandUnit::getModelByIdZAreaUserUnit($this->id_game,$id_zarea, $id_user, $id_unit);
							$_InGameLandUnit->setCount($unit_count);
						}
					}
				}
			}
			
			$this->finishProcessing();
		} catch (Exception $ex) {
			$this->_Logger->fatal($ex);
			$this->rollback();
		}
	}
}
?>