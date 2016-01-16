<?php

class ErrorDataHolder {
	
	/**
	 * @param string $full_error
	 * @param mixed $vars
	 * @return void()
	 */
	public function __construct($full_error,$vars = NULL) {
		$this->full_error = $full_error;
		$this->vars = $vars;
	}
	
	private $full_error;
	private $vars;
	
}
?>