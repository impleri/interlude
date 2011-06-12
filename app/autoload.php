<?php
/**
 * startup file
 *
 * system bootstrap and autoloader
 *
 * @package interlude
 * @subpackage framework
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
 * class name helper
 *
 * splits a class name into identifying components.
 * first is the $extension name or code (e.g. interlude or il) which is used to
 * determine the directory in /app/ to use.
 *
 * second is the (optional) $type, such as model or controller, which should be
 * a subdirectory of the extension. @see ilAutoload for predefined types.
 * A lowercase version ($typeLower) will also be returned.
 *
 * third is the class name. returned in two flavours: original camelCase as
 * $name and lowercase_with_underscores as $nameLower
 *
 * @param string class name
 * @return array of class segments
 */
function ilSplitClassName ($class) {
	// split the class name into identifying segments
	$segments = null;
	$ret = array();
	if (preg_match_all('/[A-Z][^A-Z]*/', $class, $segments)) {
		$ret['type'] = array_shift($segments);
		$ret['typeLower'] = strtolower($type);
		$ret['name'] = (count($segments)>0) ? implode('', $segments) : $type;
		$ret['nameLower'] = (count($segments)>0) ? strtolower(implode('_', $segments)) : $ret['typeLower'];
		$ret['extension'] = substr($class, 0, strpos($class, $ret['type']));
	}
	return $ret;
}

/**
 * core autoloader
 *
 * will attempt to construct a path and include it for the defined class
 * classes have two or three segments which identify its location
 * @see ilSplitClassName for the segments
 *
 * the path is inserted between the root path (/app/) and file extension (.php)
 *
 * example: the MyExtension extension uses `me` as its code. the class name
 * passed to ilAutoload is meControllerMyAppThing. its default path would be
 * /app/myextension/controllers/my_app_thing.php. if mesomething were passed,
 * the path would be /app/myextension/something.php. if it was meSomethingElse,
 * the path would be /app/myextension/something/else.php unless plugins change
 * it (@see ilExtensions::getExtensionFile)
 *
 * @param string name of class to autoload
 */
function ilAutoload ($class) {
	$segments = ilSplitClassName($class);

	// determine which extension directory (pluggable in ilExtenstions)
	$extension = ($segments['extension'] == 'il') ? 'system' : ilExtensions::getPathToExtension($segments['extension']);

	// determine which subdirectory (pluggable in ilExtenstions)
	switch ($segments['typeLower']) {
		// pluralise common types
		case 'controller':
		case 'field':
		case 'helper':
		case 'model':
		case 'panel':
		case 'parent':
		case 'table':
		case 'view':
			$directory = $segments['typeLower'] . 's';
			break;

		case 'database':
			$directory = 'db';
			break;

		// nothing if there is no type
		case $segnemts['nameLower']:
			$directory = '';
			break;

		// otherwise pass to plugin manager (default will be $type)
		default:
			$directory = ilExtensions::getExtensionFile($segments['typeLower'], $file);
			break;
	}

	// build the path
	$path = IL_ROOT . DIRECTORY_SEPARATOR . $extension . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR . $ret['nameLower'] . IL_EXT;

	// require if file found
	if(file_exists($path)) {
		require_once($path);
	}
}

// register autoloader...
spl_autoload_register('ilAutoload');

// ...and away we go!
$app = ilFactory::getApplication();
