<?php
/**
 * Functions class
 *
 * @author Christopher Roussel <christopher@impleri.net>
 * @version $Id$
 * @package Interlude
 * @filesource
 */

if (!defined('PLAY_MUSIC')) {
	die('Play it from the top, Sammie.');
}

/**#@+
 * Typecasts
 */
define('TYPE_SIMPLENAME', -2);
define('TYPE_STRING', -2);
define('TYPE_INT', -2);
define('TYPE_DEP_VERS', -2);

class ilFunctions {
	function typecast ($var, $type=TYPE_STRING, $default=null) {}
	function import ($path) {
		$pathArray = explode('.',$path);
		$count = count($pathArray)-1;
		$y=0;
		$file = SCRIPT_ROOT . DS;
		switch ($pathArray[0]) {
			case 'ext':
				$pathArray[0] = 'extensions';
				$file .= $pathArray[$y] . DS;
				$y++;
				break;
			case 'sys':
				$pathArray[0] = 'system';
				$file .= $pathArray[$y] . DS;
				$y++;
				break;
		}
		$pathArray[$count] .= '.php';
		for($x=$y; $x<=$count; $x++) {
			$piece = $pathArray[$x];
			if (!file_exists($file . $piece)) {
				$err = ilCore::getError();
				$err->setError('FILE_NOT_FOUND', $file . $piece);
				return false;
			}
			$file .= $piece . DS;
		}
		return ilCore::includeFile($file);
	}
}