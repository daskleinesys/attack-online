<?php

abstract class PhaseController extends ConstrictedController {
	protected $id_game;
	private $id_phase;
	
	/**
	 * @param int $id_user - id of the user accessing the moderation actions
	 * @param $id_phase
	 */
	public function __construct($id_user,$id_game,$id_phase) {
		parent::__construct($id_user);
		$this->id_game = intval($id_game);
		$this->id_phase = intval($id_phase);
	}
	
	/**
	 * checks if user already finished this move, returns true if user is already finished
	 * @return boolean
	 */
	protected function checkIfDone() {
		$_IGPI = ModelInGamePhaseInfo::getInGamePhaseInfo($this->id_user,$this->id_game);
		if ($_IGPI->getIsReadyForPhase($this->id_phase) == 1) return true;
		return false;
	}
	
	/**
	 * checks if user wants to get notified and sends email
	 * @return void
	 */
	protected function notifyUser() {
		// TODO: check if user wants to get notified, if so --> send mail
	}
	
	/**
	 * sets is ready to true for given phase
	 * @param $is_ready boolean
	 * @return void
	 */
	protected function fixatePhase($is_ready) {
		$_IGPI = ModelInGamePhaseInfo::getInGamePhaseInfo($this->id_user,$this->id_game);
		$_IGPI->setIsReady($this->id_phase, $is_ready);
	}
}

?>