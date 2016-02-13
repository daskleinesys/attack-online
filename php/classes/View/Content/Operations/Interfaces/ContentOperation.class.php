<?php
namespace AttOn\View\Content\Operations\Interfaces;

abstract class ContentOperation {

    public abstract function getTemplate();

	public abstract function run(array &$ata);

	protected function addCurrentGameInfo(array &$data) {
		// parse game
		$_Game = ModelGame::getCurrentGame();
		$game = array();
		$game['name'] = $_Game->getName();
		$game['round'] = $_Game->getRound();
		$game['phase'] = AttOn\Model\Game\ModelPhase::getPhase($_Game->getIdPhase())->getName();
        $data['game'] = $game;
	}

}
