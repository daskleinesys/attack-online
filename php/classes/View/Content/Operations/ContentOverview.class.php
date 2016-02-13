<?php
namespace AttOn\View\Content\Operations;
use AttOn\Controller\Game\GamesModeration;
use AttOn\Model\Atton\ModelColor;
use AttOn\Model\Atton\ModelGameMode;
use AttOn\Exceptions\GameCreationException;

class ContentOverview extends Interfaces\ContentOperation {

    public function run() {
        $this->showGameInfo();

        // parse user infos
        $iter = ModelIsInGameInfo::iterator(null,$this->id_game_logged_in);
        while ($iter->hasNext()) {
            $_IIG = $iter->next();
            $user = $this->getUserInfo($_IIG);
            $this->xtpl->assign('user',$user);
            $this->xtpl->parse('main.user');
        }

        $this->xtpl->parse('main');
        $this->xtpl->out('main');
        return true;
    }

    public function getUserInfo($_IIG) {
        $_User = ModelUser::getUser($_IIG->getIdUser());

        // money on bank
        $money = $_IIG->getMoney();

        // money from resources
        $resproduction = 0;
        $combos = array();
        $combos[RESOURCE_OIL] = 0;
        $combos[RESOURCE_TRANSPORT] = 0;
        $combos[RESOURCE_INDUSTRY] = 0;
        $combos[RESOURCE_MINERALS] = 0;
        $combos[RESOURCE_POPULATION] = 0;
        $iter = ModelGameArea::iterator($this->id_game_logged_in,$_User->getId());
        while ($iter->hasNext()) {
            $_GameArea = $iter->next();
            $resproduction += $_GameArea->getProductivity();
            $combos[$_GameArea->getIdResource()]++;
        }

        // money from traderoutes
        $traderoutes = 0;

        // money from combos
        $combo_count = $combos[RESOURCE_OIL];
        foreach ($combos as $res) {
            if ($res < $combo_count) $combo_count = $res;
        }
        $combo_money = $combo_count*4;

        // sum
        $sum = $money+$resproduction+$traderoutes+$combo_money;

        $user = array();
        $user['login'] = $_User->getLogin();
        $user['money'] = $money;
        $user['resproduction'] = $resproduction;
        $user['trproduction'] = $traderoutes;
        $user['comboproduction'] = $combo_money;
        $user['sum'] = $sum;
        return $user;
    }

}
