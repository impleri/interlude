<?php
/**
 * ilCore class
 *
 * @author Christopher Roussel <christopher@impleri.net>
 * @version $Id$
 * @package Interlude
 * @filesource
 */

if (!defined('PLAY_MUSIC')) {
	die('Play it from the top, Sammie.');
}

function ilInclude ($path) {
	static $files;
	if (!isset($files)) {
		$files = array(IL_ROOT . '/system/core.' . IL_EXT => true);
	}
	if (!isset($files[$path])) {
		$path = str_replace('ROOT', IL_ROOT, $path);
		include_once($path);
		$files[$path] = true;
	}
}

class ilCore {
	function &getCache() {
		static $cache;
		if (!isset($cache)) {
			$exts = ilCore::getExtensions();
			$ext = $exts->getProvider('_CACHE');
			$path = $ext->get('path') . '.cache';
			$name = $ext->get('name') . 'Cache';
			if (!ilCore::import($path)) {
				if (!ilCore::import('sys.cache')) {
					$err = ilCore::getError();
					$err->setError('FILE_NOT_FOUND', $path);
					return false;
				}
				else {
					$name = 'ilCache';
				}
			}
			$cache = new $name();
		}
		return $cache;
	}
	function &getLibrary() {}
	function &getConfig() {}
	function &getExt() {}
	function includeFile ($file) {}

	function init() {
		ilInclude('ROOT/system/database.' . IL_EXT);
		ilInclude('ROOT/system/cache.' . IL_EXT);
		ilInclude('ROOT/system/config.' . IL_EXT);
		$config = ilCore::getConfig();
		$config->load();

	}

}