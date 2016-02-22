<?php
namespace AttOn\Controller\Logic\Operations\Interfaces;

abstract class PhaseLogic {
	protected $id_game;
	protected $id_phase;

	protected function __construct($id_game,$id_phase) {
		$this->id_game = $id_game;
		$this->id_phase = $id_phase;
	}

	public abstract function run();

	protected function checkIfValid() {
		// check if everybody is ready
		$iter = ModelInGamePhaseInfo::iterator(null,$this->id_game);
		while ($iter->hasNext()) {
			$_IGPI = $iter->next();
			if (!$_IGPI->getIsReadyForPhase($this->id_phase)) return false;
		}
		// check if game isn't processing
		$_ModelGame = ModelGame::getGame($this->id_game);
		if ($_ModelGame->checkProcessing()) return false;
		return true;
	}

	protected function startProcessing() {
		// set game to processing
		$query = 'set_game_processing';
		$dict = array(':id_game' => $this->id_game);
		DataSource::getInstance()->epp($query,$dict);

		// begin new transaction so that we can rollback if anything unexpecting happens
		DataSource::getInstance()->beginTransaction();
	}

	protected function finishProcessing() {
		// set game to next phase
		$_ModelGame = ModelGame::getGame($this->id_game);
		$_ModelGame->moveToNextPhase();

		// update is_ready for user
		$iter = ModelInGamePhaseInfo::iterator(null,$this->id_game);
		while ($iter->hasNext()) {
			$_IGPI = $iter->next();
			$_IGPI->setIsReady($this->id_phase,false);
		}

		// TODO: write mails

		// set game to processing done
		$query = 'set_game_processing_done';
		$dict = array(':id_game' => $this->id_game);
		DataSource::getInstance()->epp($query,$dict);

		// commit everything
		DataSource::getInstance()->commit();
	}

	protected function rollback() {
		// something went wrong, rollback
		DataSource::getInstance()->rollBack();
	}

}
