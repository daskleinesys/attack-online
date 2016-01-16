<?php
class CronMain {
	private $factories;
	private $_Logger;
	private $errors = array(); // array(int id_game => string error_msg)
	
	public function __construct() {
		// factory pattern
		$this->factories = AttonToolKit::loadFactories('./classes/Controller/Logic/Factories/');
		$this->_Logger = Logger::getLogger('CronMain');
	}
	
	/**
	 * run cronjob
	 * @return void
	 */
	public function execute() {
		if (isset($_REQUEST['id_game'])) {
			$id_game = intval($_REQUEST['id_game']);
			$this->run_game($id_game);
		} else $this->check_games();
		
	}
	
	/**
	 * have there been errors?
	 * @return boolean - true if errors occured
	 */
	public function hasErrors() {
		return (!empty($this->errors));
	}
	
	/**
	 * returns errors if any
	 * @return array(int id_game => string error_msg)
	 */
	public function getErrors() {
		return $this->errors;
	}
	
	private function check_games() {
		foreach (ModelGame::getGamesForProcessing() as $id_game) {
			try {
				$this->run_game($id_game);
			} catch (ControllerException $ex) {
				$this->_Logger->fatal($ex);
				$this->errors[$id_game] = $ex;
			} catch (LogicException $ex) {
				$this->_Logger->fatal($ex);
				$this->errors[$id_game] = $ex;
			}
		}
	}
	
	private function run_game($id_game) {
		$_ModelGame = ModelGame::getGame($id_game);

		foreach ($this->factories as $factory) {
			if ($factory->getPhase() == $_ModelGame->getIdPhase()) {
				$_Logic = $factory->getOperation($id_game);
			}
		}
		
		if (!isset($_Logic)) throw new ControllerException('No operation loaded.');
		
		$_Logic->run();
		
	}
}
?>