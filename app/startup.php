<?php
/**
 * startup file
 *
 * system bootstrap and autoloader
 *
 * @package interlude
 * @subpackage app
 * @copyright Christopher Roussel <christopher@impleri.net>
 */

if (!defined('PLAY_MUSIC')) {
	die('Play it from the top, Sammie.');
}

/**
 * system root path
 * this is used later on for script includes
 */
define('IL_ROOT', dirname(__FILE__));

/**
 * core autoloader
 *
 * will attempt to construct a path and include it for the defined class
 * class and file naming convention is as follows:
 * classes have two or three segments which identify its location
 *
 * first is the extension name or code (e.g. interlude or il) which will
 * determine the directory in /app/ to use.
 *
 * second is the (optional) type name, such as model or controller. some have
 * predefined directories (see below). if that fails, it will check the plugins
 * and lastly default to the given type name.
 *
 * third is the class file name. camelCase names are separated by underscores
 * (so camelCase becomes camel_case)
 *
 * the path is then inserted between the root path (e.g. /app/) and file
 * extension (e.g. .php)
 *
 * example: the MyExtension extension uses me as its code. the class name
 * passed to ilAutoload is meControllerMyAppThing. its default path would be
 * /app/myextension/controllers/my_app_thing.php. if mesomething were passed,
 * the path would be /app/myextension/something.php. if it was meSomethingElse,
 * the path would be /app/myextension/something/else.php unless plugins change
 * it (@see ilExtensions::getExtensionFile)
 *
 * @param string $class name of class to autoload
 * @param string $fileExt comma-separated list of extensions to check (optional)
 */
function ilAutoload ($class) {
	// split the class name into identifying segments
	preg_match_all('/[A-Z][^A-Z]*/', $class, $segments);
	$extension = strtolower(array_shift($segments));
	$type = strtolower(array_shift($segments));
	$file = (count($segments)>0) ? strtolower(implode('_', $segments)) : $type;

	// determine which extension directory (pluggable in ilExtenstions)
	$extension = ($extension == 'il') ? 'system' : ilExtensions::getPathToExtension($extension);

	// determine which subdirectory (pluggable in ilExtenstions)
	switch ($type) {
		// pluralise common types
		case 'library':
			$directory = 'libraries';
			break;
		case 'model':
		case 'view':
		case 'controller':
		case 'helper':
			$directory = $type . 's';
			break;

		// nothing if there is no type
		case $file:
			$directory = '';
			break;

		// otherwise pass to plugin manager (default will be $type)
		default:
			$directory = ilExtensions::getExtensionFile($type, $file);
			break;
	}

	// build the path
	$path = IL_ROOT . DIRECTORY_SEPARATOR . $extension . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR . $file . IL_EXT;

	// require if file found
	if(file_exists($path)) {
		require_once($path);
	}
}
spl_autoload_register('ilAutoload');

// boot the system
$app = ilFactory::getApplication();
