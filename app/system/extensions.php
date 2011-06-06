<?php
/**
 * Extensions classes
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
 * Requires version comparison constant
 */
define('IL_EXTENSIONS_LT', -2);
define('IL_EXTENSIONS_LTE', -1);
define('IL_EXTENSIONS_EQ', 0);
define('IL_EXTENSIONS_GT', 1);
define('IL_EXTENSIONS_GTE', 2);
define('IL_EXTENSIONS_DEFAULT_VERSION', 100);
define('IL_EXTENSIONS_MAX_COUNT', 3);

/**
 * Extensions class
 *
 * Used when loading extensions. Interlude uses a package dependancy system
 * to ensure that extensions do not load if dependancies are not met.
 * Connected to the extensions table in db. Table schema:
 * <i>id</i> - Autonumber INT
 * <i>name</i> - Package name. VARCHAR. [UNIQUE]
 * <i>version</i> - Version number. SMALLINT UNSIGNED (0 to 65535 - if a dev gets close to that many revisions, we'll change this)
 * <i>active</i> - Is extension active? -- this is an internal field
 * <i>resolved</i> - Has extension been resolved? (this is to speed up loading process) -- this is an internal field
 * <i>provides</i> - Array of packages provided by the given package
 *		<i>name</i> - the pacakge provided, duh (meta packages begin with an underscore and are all caps)
 * <i>depends</i> - Array of packages this one depends on
 *		<i>name</i> - the package required, duh
 *		<i>version</i> - version information string (includes comparison sign, e.g. '<=99' for less than or equal to 99 [or 0.99 or 0.9.9])
 *
 * DEVELOPER API:
 * <code>$pkg = object ('name' => 'EXTENSION_NAME', 'version' => VERSION_INT, 'provides' => array(SOME_PKG, '_CONTENT', '_FORUMS' => true, 'myforums' => false), 'depends' => array('libBar' => '>35', 'libBar' => '<=99', 'libFoo', 'interlude' => '>=150') );
 * $ilDep = ilCore::getExt();
 * $ilDep->add($pkg);</code>
 * Seriously, that's it. Everything else is handled automagically.
 *
 * @package Interlude
 * @subpackage Extension-API
 */
class ilExtensions extends ilDataCacheParent {
	/**
	 * Provides meta-package
	 *
	 * Some extensions may replace core features (such as the template engine) or be needed
	 * for Interlude to function (e.g. provide output like a CMS). This string is to indicate
	 * which feature it provides.
	 *
	 * @see extenstion
	 * @var string
	 * @access private
	 */
	var $data = array();
	var $provided = array();
	var $needed = array();
	var $metaNeeded = array();
	var $resolved = true;

	// load existing data from cache/db
	function __construct() {
		parent::__construct();
		$this->load();

		// if all packages have been resolved, don't bother with re-checking to save time
		// this should be the case on initial load
		foreach ($this->data as $name => $pkg) {
			if (!$pkg->resolved) {
				$this->resolved = false;
				break;
			}
		}
	}

	// clears package info for re-check
	function reset ($packages) {
		parent::reset();
		foreach ($packages as $package) {
			$this->add($package);
		}
	}

	// simplify package version limitations
	function resolve ($pkgName, $ver2, $comp) {
		$reqdVers = $this->needed->$pkgName;

		if (isset($reqdVers[$comp])) {
			$ver1 = $reqdVers[$comp];
			if ( ($comp === IL_EXTENSIONS_LT) || ($comp === IL_EXTENSIONS_LTE) ) {
				$reqdVers[$comp] = min($ver1, $ver2);
			}
			elseif ( ($comp === IL_EXTENSIONS_GT) || ($comp === IL_EXTENSIONS_GTE) ) {
				$reqdVers[$comp] = max($ver1, $ver2);
			}
			elseif ($ver1 !== $ver2) {
				$this->setError('DEP_UNEQUAL_VERSIONS', $pkgName, $ver1, $ver2);
				return false;
			}
		}
		else {
			$reqdVers[$comp] = $ver2;
		}

		$versions = array_values($reqdVers);
		$min = min($versions);
		$max = max($versions);
		if ($min > $max) {
			$this->setError('DEP_EXCLUSIVE_VERSIONS', $pkgName, $min, $max);
			return false;
		}

		if (isset($reqdVers[IL_EXTENSIONS_EQ])) {
			$eq = $reqdVers[IL_EXTENSIONS_EQ];
			if ($min > $eq) {
				$this->setError('DEP_EXCLUSIVE_VERSIONS', $pkgName, $min, $eq);
				return false;
			}
			if ($eq > $max) {
				$this->setError('DEP_EXCLUSIVE_VERSIONS', $pkgName, $eq, $max);
				return false;
			}
		}

		$this->needed->$pkgName = $reqdVers;
		return true;
	}

	// add an extension to the dependancy resolution
	function add ($package) {
		$pass = true;
		// prevent double loading
		if (isset($this->data[$package->name])) {
			$this->setError('DEP_PKG_ALREADY_PROVIDED', $package->name);
			return false;
		}

		// first add to $data
		$package->resolved = false;
		$package->active = false;
		$this->data[$package->name] = $package;

		// finally add provided packages to $provides
		if (isset($package->provides)) {
			foreach ($package->provides as $name) {
				$meta = (strpos('_', $name) === 0);
				if ( isset($this->provided->$name) && !$meta ) {
					$this->setError('DEP_ALREADY_PROVIDED', $name);
					$pass = false;
				}
				else {
					$this->provided->$name->meta = $meta;
					if (!$meta) {
						$this->provided->$name->version = $package->version;
						$this->provided->$name->parent = $package->name;
					}
				}
			}
		}

		$this->changed = true;
		$this->resolved = false;

		return $pass;
	}

	// fill in missing meta provides with the internal core ones
	function addCoreMeta() {
		$fail = array();
		$pass = true;
		$check = array_diff($this->metaNeeded, $this->provided);
		foreach ($check as $meta => $void) {
			$load = ilCore::loadMeta($meta);
			if (!$load) {
				$fail[] = $meta;
				$pass = false;
			}
		}

		if (!empty($fail)) {
			$this->setError('DEP_MISSING_REQUIRED_META', explode(', ', $fail));
		}
		return $pass;
	}

	function getNeeded() {
		foreach ($this->data as $name => $pkg) {
			if ($pkg['active'] && isset($pkg['depends'])) {
				foreach ($pkg['depends'] as $dep => $version) {
					$comp = $this->parseComp($version);
					$need = $this->parseVers($version);
					if (isset($this->needed[$dep])) {
						$pass = $this->resolve($dep, $need, $comp);
					}
					else {
						$this->needed[$dep] = array ($comp => $version);
					}
				}
			}
		}
	}

	/**
	 * Dependancy check
	 *
	 * Runs through array of required packages. If a dependancy has not yet
	 * been loaded, allowing the node is postponed until after the resolution step.
	 *
	 *
	 * @access private
	 * @param array Dependancies
	 * @return bool True if no errors
	 */
	function init() {
		$pass = true;
		// only do full check if we need dependancies
		if ($this->resolved) {
			return $pass;
		}
		$this->getNeeded();
		if (!empty($this->needed)) {
			for ($x=0; $x < IL_EXTENSIONS_MAX_COUNT; $x++) {
				$remove = array();
				$pass = true; // default to pass until a problem is encountered

				// iterate through all dependancies, checking that they are provided in $data and version is acceptable
				foreach ($this->needed as $package => $version) {
					if (!$this->compare($package, $version)) {
						$this->setError('DEP_VERSION_NOT_MET', $package);
						$remove[] = $package;
						$pass = false;
					}
				}

				// if no problem found, stop!
				if ($pass) {
					break;
				}

				// else, remove packages that depend on the missing dependancies
				$packages = $this->data;
				foreach ($packages as $name => $pkg) {
					if (isset($pkg['depends'])) {
						$deps = array_keys($pkg['depends']);
						$inter = array_intersect($deps, $remove);
						if (!empty($inter)) {
							$this->setError('DEP_PKG_MISSING_DEP', $name);
							$this->setActive($name, false);
						}
					}
				}
				$this->reset($packages);
				$this->getNeeded();
			}
		}

		// finally, check that we have necessary meta-packages
		if ($pass) {
			$pass = $this->addCoreMeta();
		}
		else {
			$this->setError('DEP_CHECK_EXCEED_COUNT');
		}

		// if everything passes, set the resolved field for everything to true to bypass this later
		if ($pass) {
			$this->setResolved(true);
		}

		return $pass;
	}

	/**
	 * Compare version to loaded package
	 *
	 * Checks node for unmet dependancies. Used for package loading.
	 *
	 * @access public
	 * @return bool True if unresolved dependancies
	 */
	function compare ($package, $version) {
		$check = false;
		$min = min($version);
		$max = max($version);
		$eq = (isset($version[IL_EXTENSIONS_EQ])) ? $version[IL_EXTENSIONS_EQ] : null;
		// package is in system
		if (isset($this->data[$package])) {
			$have = $this->data[$package]['version'];

			// faster check (slightly less accurate but also much faster
			if ( ($have > $min) && ($have < $max)) {
				$check = (isset($eq)) ? ($eq == $have) : true;
			}

			if (!$check) {
				foreach ($version as $comp => $need) {
					switch ($comp) {
						case IL_EXTENSIONS_LT:
							$check = ($have < $need);
							break;
						case IL_EXTENSIONS_LTE:
							$check = ($have <= $need);
							break;
						case IL_EXTENSIONS_EQ:
							$check = ($have == $need);
							break;
						case IL_EXTENSIONS_GTE:
						default:
							$check = ($have >= $need);
							break;
						case IL_EXTENSIONS_GT:
							$check = ($have > $need);
							break;
					}
				}
			}
		}
		return $check;
	}

	/**
	 * Comparison Converter
	 *
	 * Converts the text version
	 *
	 * @access private
	 * @param string Version string
	 * @return int Constant value of comparison needed (default is less than or equal to)
	 */
	function parseComp ($version) {
		$nums = array ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
		$version = trim(str_replace($nums, '', $version));
		switch ($version) {
			case '<=':
			case 'lte':
				$ret = IL_EXTENSIONS_LTE;
				break;
			case '<':
			case 'lt':
				$ret = IL_EXTENSIONS_LT;
				break;
			case '==':
			case '=':
			case 'eq':
				$ret = IL_EXTENSIONS_EQ;
				break;
			case '>':
			case 'gt':
				$ret = IL_EXTENSIONS_GT;
				break;
			case '>=':
			case 'gte':
			default:
				$ret = IL_EXTENSIONS_GTE;
				break;
		}
		return $ret;
	}

	/**
	 * Version Converter
	 *
	 * Converts the text version to a float val
	 *
	 * @param string Version string
	 * @return float Version number (default is 0001)
	 */
	function parseVers ($version) {
		$ops = array ('<', '>', '=', 'g', 'l', 't', 'e', 'q', 'a');
		$version = trim(str_replace($ops, '', $version));
		return (empty($version)) ? IL_EXTENSIONS_DEFAULT_VERSION : intval($version);
	}

	function setResolved ($res=true) {
		foreach ($this->data as $name => $pkg) {
			$pkg['resolved'] = $res;
		}
		$this->changed = true;
	}

	function setActive ($package, $act=true) {
		if (isset($this->data[$package])) {
			$this->data[$package]->['active'] = $act;
			$this->changed = true;
		}
	}

}

class ilParentExtension extends ilParent {
	var $name = '';
	var $version = IL_EXTENSIONS_DEFAULT_VERSION;
	var $depends = array();
	var $provides = array();

	function validate() {
		// strip illegal characters from name
		$this->name = ilFunctions::typecast($this->name, TYPE_SIMPLENAME);
		$this->version = ilFunctions::typecast($this->version, TYPE_INT, IL_EXTENSIONS_DEFAULT_VERSION);
		if (!empty($this->provides)) {
			$cleanProv = array();
			foreach ($this->provides as $pkg) {
				$cleanProv[] = ilFunctions::typecast($pkg, TYPE_SIMPLENAME);
			}
		}
		$this->provides = $cleanProv;
		if (!empty($this->depends)) {
			$cleanDeps = array();
			foreach ($this->depends as $pkg => $vers) {
				$pkg = ilFunctions::typecast($pkg, TYPE_SIMPLENAME);
				$cleanDeps[$pkg] = ilFunctions::typecast($vers, TYPE_DEP_VERS);
			}
		}
		$this->depends = $cleanDeps;
	}
}

class ilExtension extends ilParentExtension {
	var $name = '';
	var $active = 0;

	function add() {
		$this->validate();
		$ext = ilCore::getExt();
		$ext->add($this);
	}

	function __construct() {} // runs before final dependancy resolution

	function __destruct() {} // updates extension config

	function init() {} // runs after dependancy resolution

	function activate() {} // self-explanatory

	function deactivate() {}

}
