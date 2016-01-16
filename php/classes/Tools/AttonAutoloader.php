<?php
namespace AttOn\Tools;

spl_autoload_register(array('AttOn\Tools\Autoloader', 'autoload'));

class Autoloader {
    
	/**
	 * Loads a class.
	 * @param string $className The name of the class to load.
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
    
}
