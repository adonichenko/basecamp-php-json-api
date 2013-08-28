<?php
/**
 * Файл: ./runner.php
 * 
 * @package library.1.0
 * @author Alexander Donichenko (adonichenko@gmail.com)
 */
set_time_limit(0);
error_reporting(E_ALL);
setlocale(LC_ALL, 'ru_RU');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('memory_limit', '256M');
ini_set('magic_quotes_runtime', 0);
ini_set('magic_quotes_gpc', 0);

define('LIB_CACHE', __DIR__ . '/cache');
define('LIB_CLASSES', __DIR__ . '/classes');
define('LIB_PLUGINS', __DIR__ . '/plugins');
define('LIB_INI', __DIR__ . '/classes/ini');

/**
 * Autoloading classes
 * 
 * @param string $class
 */
function __autoload($class) {
	if (class_exists($class, false) === false) {
		require_once LIB_CLASSES . '/class.' . strtolower($class) . '.php';
	}
}

?>
