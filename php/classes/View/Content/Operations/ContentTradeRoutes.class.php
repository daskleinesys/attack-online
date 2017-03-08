<?php
namespace Attack\View\Content\Operations;

use Attack\Controller\Game\Moves\TradeRoutesController;
use Attack\Model\Game\ModelGame;
use Attack\Model\User\ModelUser;
use Attack\View\Content\Operations\Interfaces\ContentOperation;

class ContentTradeRoutes extends ContentOperation {

    public function getTemplate() {
        return 'traderoutes';
    }

    public function run(array &$data) {
        $data['template'] = $this->getTemplate();
        $this->addCurrentGameInfo($data);

        if (!$this->checkFixate($data, PHASE_TRADEROUTES)) {
            $this->handleInput($data);
        }

        $this->checkCurrentPhase($data, PHASE_TRADEROUTES);
    }

    private function handleInput(array &$data) {
        if (empty($_POST)) {
            return;
        }
        $controller = new TradeRoutesController(ModelUser::getCurrentUser()->getId(), ModelGame::getCurrentGame()->getId());

        // fixating sea move
        if (isset($_POST['fixate_traderoutes'])) {
            $controller->finishMove();
            $this->checkFixate($data, PHASE_TRADEROUTES);
            return;
        }
    }

}
