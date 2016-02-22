<?php
namespace AttOn\Tools;

spl_autoload_register(array('AttOn\Tools\Autoloader', 'autoload'));

class Autoloader {

	/**
	 * Loads a class.
     *
	 * @param string $className The name of the class to load.
     * @return void
	 */
	public static function autoload($className) {
        $path = explode('\\', $className);

        if (isset($path[0]) === false) {
            return false;
        }

        $filename = array_slice($path, -1, 1);

        if ($path[0] !== 'AttOn') {
            return false;
        } else {
            $path = array_slice($path, 1, -1);
        }

        $classFile = __DIR__
                    . DIRECTORY_SEPARATOR
                    . '..'
                    . DIRECTORY_SEPARATOR
                    . implode(DIRECTORY_SEPARATOR, $path)
                    . DIRECTORY_SEPARATOR
                    . $filename[0]
                    . '.class.php';

        if (is_file($classFile) === true && class_exists($className) === false) {
            require_once $classFile;
        }
	}

    /**
     * instanciates all classes found in a given folder and returns them
     *
     * @param $folder string with PATH
     * @param $namespace string namespace for given classes
     * @throws \Exception
     * @return array(Object)
     */
	public static function loadFactories($folder, $namespace) {
		$result = array();
		// load all remaining classes
		$dir_handler = opendir($folder);
		if (!$dir_handler) {
            throw new \Exception('Invalid dir-path given: ' . $folder);
        }
		while (false !== ($file = readdir($dir_handler))) {
			if ($file == '.' || $file == '..') {
                continue;
            }
			if (!preg_match('/(.class.php)$/', $file)) {
                continue;
            }
			$class = substr($file, 0, strlen($file) - 10);
            $class = $namespace . $class;
			$result[] = new $class();
		}
		return $result;
	}

}
