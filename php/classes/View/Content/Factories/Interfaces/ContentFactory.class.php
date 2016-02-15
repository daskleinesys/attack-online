<?php
namespace AttOn\View\Content\Factories\Interfaces;
use AttOn\Model;
use AttOn\Exceptions\SessionException;

abstract class ContentFactory {

    /**
     * returns the shortname of the operation/action
     *
     * @return string
     */
	public abstract function getName();

    /**
     * returns ContentOperation object for the corresponding action
     * throws SessionException if invalid user status
     *
     * @throws SessionException
     * @return ContentOperation
     */
	public abstract function getOperation();

    protected function checkAuth($required_session) {
        $current_user = Model\User\ModelUser::getCurrentUser();
        $status = $current_user->getStatus();

        $current_game = Model\Game\ModelGame::getCurrentGame();

		switch ($required_session) {
			case CHECK_SESSION_NONE:
				break;
			case CHECK_SESSION_USER:
				if ($status !== STATUS_USER_ACTIVE && $status !== STATUS_USER_ADMIN && $status !== STATUS_USER_MODERATOR) {
                    throw new SessionException('No active user.');
                }
				break;
			case CHECK_SESSION_ADMIN:
				if ($status !== STATUS_USER_ADMIN) {
                    throw new SessionException('Non-admin tried to login at content: ' . $this->getName() . '.');
                }
				break;
			case CHECK_SESSION_MOD:
				if ($status !== STATUS_USER_ADMIN && $status !== STATUS_USER_MODERATOR) {
                    throw new SessionException('Non-moderator tried to login at content: ' . $this->getName() . '.');
                }
				break;
			case CHECK_SESSION_GAME:
				if ($current_game === null) {
                    throw new SessionException('Choose game first');
                }
				break;
			case CHECK_SESSION_GAME_START:
				if ($current_game === null) {
                    throw new SessionException('Choose game first');
                }
				if ($current_game->getStatus() !== GAME_STATUS_STARTED) {
                    throw new SessionException('Game is not in the starting phase.');
                }
				break;
			case CHECK_SESSION_GAME_RUNNING:
				if ($current_game === null) {
                    throw new SessionException('Choose game first');
                }
				if ($current_game->getStatus() !== GAME_STATUS_RUNNING) {
                    throw new SessionException('Game is not running.');
                }
				break;
			default:
				throw new SessionException('Invalid Session Type.');
        }
    }

}
