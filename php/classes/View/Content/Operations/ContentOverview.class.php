<?php
namespace AttOn\View\Content\Operations;

use AttOn\Model\Atton\InGame\ModelGameArea;
use AttOn\Model\Game\ModelGame;
use AttOn\Model\User\ModelIsInGameInfo;
use AttOn\Model\User\ModelUser;

class ContentOverview extends Interfaces\ContentOperation {

    public function getTemplate() {
        return 'overview';
    }

    public function run(array &$data) {
        $data['template'] = $this->getTemplate();
        $this->addCurrentGameInfo($data);

        // parse user infos
        $users = array();
        $iter = ModelIsInGameInfo::iterator(null, ModelGame::getCurrentGame()->getId());
        while ($iter->hasNext()) {
            $ingame = $iter->next();
            $user = $this->getUserInfo($ingame);
            $users[] = $user;
        }
        $data['users'] = $users;
    }

    public function getUserInfo($ingame) {
        $user = ModelUser::getUser($ingame->getIdUser());

        // money on bank
        $money = $ingame->getMoney();

        // money from resources
        $resproduction = 0;
        $combos = array();
        $combos[RESOURCE_OIL] = 0;
        $combos[RESOURCE_TRANSPORT] = 0;
        $combos[RESOURCE_INDUSTRY] = 0;
        $combos[RESOURCE_MINERALS] = 0;
        $combos[RESOURCE_POPULATION] = 0;
        $iter = ModelGameArea::iterator($user->getId(), ModelGame::getCurrentGame()->getId());
        while ($iter->hasNext()) {
            $area = $iter->next();
            $resproduction += $area->getProductivity();
            $combos[$area->getIdResource()]++;
        }

        // money from traderoutes
        $traderoutes = 0;

        // money from combos
        $combo_count = $combos[RESOURCE_OIL];
        foreach ($combos as $res) {
            if ($res < $combo_count) {
                $combo_count = $res;
            }
        }
        $combo_money = $combo_count * 4;

        // sum
        $sum = $money + $resproduction + $traderoutes + $combo_money;

        $userData = array();
        $userData['login'] = $user->getLogin();
        $userData['money'] = $money;
        $userData['resproduction'] = $resproduction;
        $userData['trproduction'] = $traderoutes;
        $userData['comboproduction'] = $combo_money;
        $userData['sum'] = $sum;
        return $userData;
    }

}
