<?php
namespace AttOn\View\Content\Operations\Interfaces;

use AttOn\Model\Atton\ModelPhase;
use AttOn\Model\Game\ModelGame;
use AttOn\Model\User\ModelInGamePhaseInfo;
use AttOn\Model\User\ModelUser;

abstract class ContentOperation {

    public abstract function getTemplate();

    public abstract function run(array &$ata);

    protected function addCurrentGameInfo(array &$data) {
        // parse game
        $gameModel = ModelGame::getCurrentGame();
        $game = array();
        $game['name'] = $gameModel->getName();
        $game['round'] = $gameModel->getRound();
        $game['phase'] = ModelPhase::getPhase($gameModel->getIdPhase())->getName();
        $data['currentGame'] = $game;
    }

    protected function checkFixate(array &$data, $id_phase) {
        $igpi = ModelInGamePhaseInfo::getInGamePhaseInfo(ModelUser::getCurrentUser()->getId(), ModelGame::getCurrentGame()->getId());
        return $data['turnFixated'] = ($igpi->getIsReadyForPhase((int)$id_phase)) ? true : false;
    }

}
