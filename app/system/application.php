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

class ilApplication {
	/* preload(): loads required modules, then finds all other modules
	* and loads each init script (init.php in their main folder)
	*
	* NOTE: none of these init scripts should run anything.  These will load
	* during the load phase with everything else.  Use the feeds module as an
	* example.
	*
	* Each module should be self-contained under a single directory here.
	* How mod authors organize within there is up to them.
	*
	* What gets loaded here: $db, $config, $plugins, $hooks;
	*/
	function preload() {
		// run core preload


		// preload modules
		foreach ($config->modules as $module) {
			include_once($config->sys_path($module . '/init'));
		}

		return true;
		}

	/* load(): loads all modules (instantiates the main class)
	* NOTE: none of these init scripts should run anything.  These will load
	* during the load phase with everything else.
	*
	* What gets loaded here: everything that is installed
	*/
	function load() {
		global $config;

		// load core modules first
		foreach ($config->core_modules as $module) {
			$$module = new $module();
		}

		// load remaining modules
		foreach ($config->modules as $module) {
			$$module = new $module();
		}

		// combine lists of modules together
		$config->set('modules', $config->modules + $config->core_modules);
		$config->unset('core_modules');

		return true;
	}

	// run(): processes all loaded modules
	function run()
	{
		global $config;

		foreach ($config->modules as $module) {
			if (method_exists($module, 'process')) {
				$$module->process();
			}
		}
	}

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
