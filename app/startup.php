<?php
/** file: startup
 * Begin: 01/01/2006
 * $Revision: 11 $
 * $Date: 2008-10-26 22:49:29 +0000 (Sun, 26 Oct 2008) $
 *
 * description: autoloader class
 */

if (!defined('PLAY_MUSIC')) {
	die('Play it from the top, Sammie.');
}

// correct root path
define('IL_ROOT', dirname(__FILE__));

require_once(IL_ROOT . '/system/core.' . IL_EXT);

ilCore::init();



class interlude {
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
}


interlude::process();
