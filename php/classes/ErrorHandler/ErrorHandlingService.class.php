<?php

class ErrorHandlingService {
	
	private $debug_level;
	private $logger;
	private $full_error;
	private $vars;
	
	/**
	 * Instance this class once to use personalised error handling
	 * @param int $debug_level (0 = debug-mode, 1 = live_mode)
	 * @return void()
	 */
	public function __construct($debug_level = 1) {
		if (!is_int($debug_level)) return false;
		
		// Create logger link
		$this->logger = Logger::getLogger('ErrorHandlingService');
		
		// Populate class variables
		$this->debug_level = $debug_level;
		
		// Take over from PHP error handling
		set_error_handler(array($this, 'handle_error'));
		
		// check if data holder class exists -> if not, load it
		if (!class_exists('ErrorDataHolder')) {
			require_once('ErrorDataHolder.class.php');
		}
	}
	
	/**
	 * @param int $type
	 * @param string $string
	 * @param string $file
	 * @param int $line
	 * @param mixed $vars
	 * @return void()
	 */
	public function handle_error($type, $string, $file, $line, $vars) {
		
		
		// store error infos as member variables
		$this->full_error = "Error[$type]: " . $string . ' in ' . $file . ' on line ' . $line;
		$this->vars = $vars;
		
		// Decide which type of error it is, and handle appropriately
		switch ($type) {
			default:
			case E_WARNING:
				$this->reportError(1);
				break;
			case E_NOTICE:
				$this->reportError(1);
				break;
			case E_USER_WARNING:
				$this->reportError(1);
				break;
			case E_USER_NOTICE:
				$this->reportError(1);
				break;
				
			case E_USER_ERROR:
				$this->reportError(2);
				echo 'There has been an error. Sorry for the inconvenience.<br/>Please come back later.<br/>If it is later allready please contact: atton@thespielplatz.com';
				// Stop application
				die();
			case E_ERROR:
				$this->reportError(2);
				echo 'There has been an error. Sorry for the inconvenience.<br/>Please come back later.<br/>If it is later allready please contact: atton@thespielplatz.com';
				// Stop application
				die();
		}

	}
	
	/**
	 * $log_lvl sets the lvl in the log4php class
	 * @param int $log_lvl (1 = warn, 2 = error)
	 * @return void()
	 */
	private function reportError($log_lvl) {
		// log error
		// create data object
		$data = new ErrorDataHolder($this->full_error);
		switch($log_lvl) {
			case 1:
				$this->logger->warn($data);
				break;
			default:
			case 2:
				$this->logger->error($data);
		}
		// dismiss data object
		unset($data);
		
		// echo error (if in debug mode)
		switch ($this->debug_level) {
			case 0:
				// echo error
				echo $this->full_error . '<br />';
				// @TODO show error var_dump -> mass data?
				// print_r($this->vars);
				echo '<br />';
				break;
			default:
			case 1:
		}
	}
}

?>