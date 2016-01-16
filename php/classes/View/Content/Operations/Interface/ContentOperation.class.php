<?php
abstract class ContentOperation {
	private $xtpl_basic;
	protected $xtpl;
	protected $id_user_logged_in;
	protected $id_game_logged_in;
	protected $_Logger;
	
	public function __construct($id_user,$id_game,$content,$session_type) {
		$this->_Logger = Logger::getLogger($content);
		$this->id_user_logged_in = $id_user;
		$this->id_game_logged_in = $id_game;
		
		if ($id_user != null) {
			$status = ModelUser::getUser($id_user)->getStatus();
		} else $status = null;
		
		switch ($session_type) {
			case CHECK_SESSION_NONE:
				break;
			case CHECK_SESSION_USER:
				if (($status == null) || (($status != STATUS_USER_ACTIVE) && ($status != STATUS_USER_ADMIN) && ($status != STATUS_USER_MODERATOR)))  throw new SessionException('No active user.');
				break;
			case CHECK_SESSION_ADMIN:
				if (($status == null) || ($status != STATUS_USER_ADMIN))  throw new SessionException('Non-admin tried to login at content: ' . $content . '.');
				break;
			case CHECK_SESSION_MOD:
				if (($status == null) || (($status != STATUS_USER_ADMIN) && ($status != STATUS_USER_MODERATOR)))  throw new SessionException('Non-moderator tried to login at content: ' . $content . '.');
				break;
			case CHECK_SESSION_GAME:
				if ($this->id_game_logged_in == null) throw new SessionException('Choose game first');
				break;
			case CHECK_SESSION_GAME_START:
				if ($this->id_game_logged_in == null) throw new SessionException('Choose game first');
				$_Game = ModelGame::getGame($this->id_game_logged_in);
				if ($_Game->getStatus() != GAME_STATUS_STARTED) throw new SessionException('Game is not in the starting phase.');
				break;
			case CHECK_SESSION_GAME_RUNNING:
				if ($this->id_game_logged_in == null) throw new SessionException('Choose game first');
				$_Game = ModelGame::getGame($this->id_game_logged_in);
				if ($_Game->getStatus() != GAME_STATUS_RUNNING) throw new SessionException('Game is not running.');
				break;
			default:
				throw new SessionException('Invalid Session Type.');
		}
		
		$this->xtpl_basic = new XTemplate('./xtpl/content/basic_content.xtpl');
		$this->xtpl_basic->assign('content',$content);
		
		$this->xtpl = new XTemplate('./xtpl/content/' . $content . '.xtpl');
		$this->xtpl->assign('content',$content);
		
	}
	
	public function beginContent() {
		$this->xtpl_basic->parse('begin_content');
		$this->xtpl_basic->out('begin_content');
	}
	
	public abstract function run();
	
	public function showErrorMsg($msg) {
		$this->xtpl_basic->assign('error_msg',$msg);
		$this->xtpl_basic->parse('error_msg');
		$this->xtpl_basic->out('error_msg');
	}
	
	public function showLoginError($msg) {
		$this->xtpl_basic->assign('error_msg',$msg);
		$this->xtpl_basic->parse('login_error');
		$this->xtpl_basic->out('login_error');
	}
	
	public function endContent() {
		$this->xtpl_basic->parse('end_content');
		$this->xtpl_basic->out('end_content');
	}
	
	public function showContentError($msg) {
		$this->xtpl_basic->assign('error_msg',$msg);
		$this->xtpl_basic->parse('content_error');
		$this->xtpl_basic->out('content_error');
	}
	
	public function showContentInfo($msg) {
		$this->xtpl_basic->assign('msg',$msg);
		$this->xtpl_basic->parse('content_info');
		$this->xtpl_basic->out('content_info');
	}
	
	protected function showGameInfo() {
		// parse game
		$_Game = ModelGame::getGame($this->id_game_logged_in);
		$game = array();
		$game['name'] = $_Game->getName();
		$game['round'] = $_Game->getRound();
		$game['phase'] = ModelPhase::getPhase($_Game->getIdPhase())->getName();
		$this->xtpl_basic->assign('game',$game);
		$this->xtpl_basic->parse('game_info');
		$this->xtpl_basic->out('game_info');
	}
}
?>