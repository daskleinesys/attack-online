<?php

class SelectStartController extends PhaseController {
	private $_Logger;
	private $error = false;
	
	/**
	 * @param int $id_user - id of the user accessing the moderation actions
	 * @return SelectStartController
	 */
	public function __construct($id_user,$id_game) {
		parent::__construct($id_user,$id_game,PHASE_SELECTSTART);
		$this->_Logger = Logger::getLogger('SelectStartController');
	}
	
	/**
	 * user selected areas
	 * @param $option_number int
	 * @param $areas array(int id_area)
	 * @throws ControllerException
	 * @return void
	 */
	public function selectStartAreas($id_set,$option_number,$areas) {
		if ($this->error) return;
		
		// check if user has already finished this move
		if ($this->checkIfDone()) {
			$this->error = true;
			throw new ControllerException('You have already finished this move.');
		}
		
		// check if regions are in the set option
		$supported_areas = ModelStartRegion::getRegionsForSetAndOption($id_set, $option_number);
		foreach ($areas as $id_area) {
			if (!isset($supported_areas[$id_area])) {
				$this->error = true;
				throw new ControllerException('The area '.$id_area.' is not in your set and option-number.');
			}
		}
		
		// check if correct number of areas selected
		$_StartRegion = array_shift($supported_areas);
		$id_option_type = $_StartRegion->getIdOptionType();
		$_OptionType = ModelOptionType::getOptionType($id_option_type);
		if ($_OptionType->getCountries() != count($areas)) {
			$this->error = true;
			throw new ControllerException('Please choose the correct amount of areas.');
		}
		
		// eintragen
		$_Move = ModelSelectStartMove::getSelectStartMoveForUser($this->id_game, $this->id_user);
		$zareas = array();
		foreach ($areas as $id_area) {
			$_GameArea = ModelGameArea::getGameAreaForArea($this->id_game, $id_area);
			$zareas[] = $_GameArea->getId();
		}
		$_Move->setRegions($option_number, $zareas);
	}
	
	/**
	 * fixates the move if no error occured
	 * @throws ControllerException
	 * @return void
	 */
	public function finishMove() {
		if ($this->error) return;
		// check if every option has taken countries
		$_Move = ModelSelectStartMove::getSelectStartMoveForUser($this->id_game, $this->id_user);
		$regions_selected = $_Move->getRegions();
		// array(int option_number => array(int id_zarea))
		
		// get Model Data
		$_IIG = ModelIsInGameInfo::getIsInGameInfo($this->id_user, $this->id_game);
		$id_set = $_IIG->getIdStartingSet();
		$regions = ModelStartRegion::getRegionsForSet($id_set);
		// array(int id_opttype => array(int option_number => array(int id_area => ModelStartRegion)))
		
		foreach ($regions as $opttype) {
			foreach ($opttype as $opt_number => $areas) {
				if (!isset($regions_selected[$opt_number])) throw new ControllerException('Choose countries first.');
			}
		}
		
		$this->fixatePhase(true);
	}
}

?>