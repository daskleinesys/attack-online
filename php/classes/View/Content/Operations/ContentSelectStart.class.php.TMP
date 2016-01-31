<?php
class ContentSelectStart extends ContentOperation {
	
	private $id_set;
	private $regions;

	public function run() {
		$this->showGameInfo();
		
		// get Model Data
		$_IIG = ModelIsInGameInfo::getIsInGameInfo($this->id_user_logged_in, $this->id_game_logged_in);
		$this->id_set = $_IIG->getIdStartingSet();
		$this->regions = ModelStartRegion::getRegionsForSet($this->id_set); // array(int id_opttype => array(int option_number => array(int id_area => ModelStartRegion)))
		
		// update moves
		if (isset($_POST['selectstart'])) $this->selectOption(false);
		elseif (isset($_POST['fixate_start'])) $this->selectOption(true);
		
		// parse moves
		$this->checkFixate();
		$this->parseOptions();
		
		$this->xtpl->parse('main.startcountries');
		$this->xtpl->parse('main');
		$this->xtpl->out('main');
	}
	
	private function selectOption($fixate) {
		$_MoveController = new SelectStartController($this->id_user_logged_in,$this->id_game_logged_in);
		
		foreach ($this->regions as $option_types) {
			foreach ($option_types as $option_number => $options) {
				if (isset($_POST['countries_'.$option_number])) {
					try {
						$_MoveController->selectStartAreas($this->id_set, $option_number, $_POST['countries_'.$option_number]);
					} catch (ControllerException $ex) {
						$this->_Logger->error($ex);
						$this->showErrorMsg($ex->getMessage());
					}
				}
			}
		}
		
		try {
			if ($fixate) $_MoveController->finishMove();
		} catch (ControllerException $ex) {
			$this->showErrorMsg($ex->getMessage());
		}
	}
	
	private function parseOptions() {
		
		foreach ($this->regions as $id_option_type => $option_types) {
			$_OptionType = ModelOptionType::getOptionType($id_option_type);
			$opttype = array();
			$opttype['units'] = $_OptionType->getUnits();
			$opttype['countries'] = $_OptionType->getCountries();
			$this->xtpl->assign('opttype',$opttype);
			
			foreach ($option_types as $option_number => $options) {
				$count = 0;
				$this->xtpl->assign('option_number',$option_number);
				foreach ($options as $id_area => $_StartRegion) {
					$count++;
					
					// get area infos
					$_Area = ModelArea::getArea($id_area);
					$country = array();
					$country['id_area'] = $id_area;
					$country['nr'] = $_Area->getNumber();
					$country['name'] = $_Area->getName();
					$this->xtpl->assign('country',$country);
					
					// check if country already selected
					$_GameArea = ModelGameArea::getGameAreaForArea($this->id_game_logged_in, $id_area);
					$id_zarea = $_GameArea->getId();
					$_ModelMove = ModelSelectStartMove::getSelectStartMoveForUser($this->id_game_logged_in, $this->id_user_logged_in);
					$checked = ($_ModelMove->checkIfAreaIsSelected($option_number, $id_zarea)) ? 'checked' : '';
					$this->xtpl->assign('checked',$checked);
					
					$this->xtpl->parse('main.startcountries.option_type.select.option');
				}
				$this->xtpl->assign('country_count',$count);
				$this->xtpl->parse('main.startcountries.option_type.select');
			}
			
			$this->xtpl->parse('main.startcountries.option_type');
		}
		
	}

	private function checkFixate() {
		$_IGPI = ModelInGamePhaseInfo::getInGamePhaseInfo($this->id_user_logged_in,$this->id_game_logged_in);
		if ($_IGPI->getIsReadyForPhase(PHASE_SELECTSTART) == 1) {
			$this->xtpl->assign('disabled','disabled');
			return;
		}
		$this->xtpl->assign('disabled','');
		$this->xtpl->parse('main.startcountries.submit');
		$this->xtpl->parse('main.startcountries.fixate');
	}
}

?>