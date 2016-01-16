<?php

class AttonToolKit {

	public static function loadFactories($folder) {
		$result = array();
		// load all remaining classes
		$dir_handler = opendir($folder);
		if (!$dir_handler) throw new Exception('Invalid dir-path given: ' . $folder);
		while (false !== ($file = readdir($dir_handler))) {
			if ($file == '.' || $file == '..') continue;
			if (!preg_match('/(.class.php)$/',$file)) continue;
			$class = substr($file,0,strlen($file)-10);
			$result[] = new $class();
		}
		return $result;
	}
	
}

?>