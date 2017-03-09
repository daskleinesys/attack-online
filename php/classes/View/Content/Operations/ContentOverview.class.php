<?php
namespace Attack\View\Content\Operations;

use Attack\Model\Game\ModelGame;
use Attack\Model\User\ModelIsInGameInfo;
use Attack\Model\User\ModelUser;
use Attack\Tools\UserViewHelper;

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
            /** @var ModelIsInGameInfo $userIngame */
            $userIngame = $iter->next();
            $user = $this->getUserInfo($userIngame);
            $users[] = $user;
        }
        $data['users'] = $users;
    }

    public function getUserInfo(ModelIsInGameInfo $userIngame) {
        $user = ModelUser::getUser($userIngame->getIdUser());
        $productionData = UserViewHelper::getCurrentProductionForUserInGame($user->getId(), ModelGame::getCurrentGame()->getId());

        $userData = array();
        $userData['login'] = $user->getLogin();
        $userData['money'] = $productionData['money'];
        $userData['resproduction'] = $productionData['resproduction'];
        $userData['trproduction'] = $productionData['trproduction'];
        $userData['comboproduction'] = $productionData['comboproduction'];
        $userData['sum'] = $productionData['sum'];
        return $userData;
    }

}
