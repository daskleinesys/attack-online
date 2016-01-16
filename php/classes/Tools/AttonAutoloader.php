<?php



spl_autoload_register(array('AttonAutoloader', 'autoload'));

class AttonAutoloader {
	private static $classes = array();
	
	/**
	 * loads all classes in given directory (recursive)
	 */
	public static function init($path) {
		// load all classes
		self::readDir($path);
	}
	
	private static function readDir($path) {
		$dir_handler = opendir($path);
		while (false !== ($file = readdir($dir_handler))) {
			if ($file == '.' || $file == '..') continue;
			
			if (is_dir($path . $file)) {
				$path_2 = $path . $file . '/';
				self::readDir($path_2);
				continue;
			}
			
			if (!preg_match('/(.class.php)$/',$file)) continue;
			
			$file_name = substr($file, 0, strlen($file) - 10);
			
			self::$classes[$file_name] = $path . $file;
		}
	}
	
	/**
	 * Loads a class.
	 * @param string $className The name of the class to load.
	 */
	public static function autoload($className) {
		//echo 'looking for: ' . $className . '<br/>';
		if(isset(self::$classes[$className])) {
			//echo 'found: ' . self::$classes[$className] . '<br/>';
			include self::$classes[$className];
		}
	}
}

AttonAutoloader::init('./classes/');


?>