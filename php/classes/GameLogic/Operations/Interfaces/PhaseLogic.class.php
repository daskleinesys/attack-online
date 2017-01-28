<?php
namespace Attack\GameLogic\Operations\Interfaces;

use Attack\Database\SQLConnector;
use Attack\Model\Game\ModelGame;
use Attack\Model\User\ModelInGamePhaseInfo;

abstract class PhaseLogic {
	protected $id_game;
	protected $id_phase;

	protected function __construct($id_game, $id_phase) {
		$this->id_game = $id_game;
		$this->id_phase = $id_phase;
	}

	public abstract function run();

	protected function checkIfValid() {
		// check if everybody is ready
		$iter = ModelInGamePhaseInfo::iterator(null, $this->id_game);
		while ($iter->hasNext()) {
			$igpi = $iter->next();
			if (!$igpi->getIsReadyForPhase($this->id_phase)) {
				return false;
			}
		}
		// check if game isn't processing
		$game = ModelGame::getGame($this->id_game);
		if ($game->checkProcessing()) {
			return false;
		}
		return true;
	}

	protected function startProcessing() {
        $game = ModelGame::getGame($this->id_game);
        $game->setProcessing(true);

		// begin new transaction so that we can rollback if anything unexpecting happens
		SQLConnector::getInstance()->beginTransaction();
	}

	protected function finishProcessing() {
		// set game to next phase
		$game = ModelGame::getGame($this->id_game);
		$game->moveToNextPhase();

		// update is_ready for user
		$iter = ModelInGamePhaseInfo::iterator(null, $this->id_game);
		while ($iter->hasNext()) {
			$igpi = $iter->next();
			$igpi->setIsReady($this->id_phase, false);
		}

		// TODO: write mails

		// set game to processing done
        $game = ModelGame::getGame($this->id_game);
        $game->setProcessing(false);

		// commit everything
		SQLConnector::getInstance()->commit();
	}

	protected function rollback() {
		// something went wrong, rollback
		SQLConnector::getInstance()->rollBack();
	}

}
