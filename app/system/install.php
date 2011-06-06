<?php
/**
 * Installer class
 *
 * @author Christopher Roussel <christopher@impleri.net>
 * @version $Id$
 * @package Interlude-Example
 * @filesource
 */

if (!defined('PLAY_MUSIC')) {
	die('Play it from the top, Sammie.');
}

class ilInstallerDatabase extends ilDatabase {}

class ilInstallerParent extends ilParent {
	var $extension;
	var $oldVersion;
	// loads info of extension being installed and saves for later
	function __construct() {
		$path = ilFunctions::cleanPath(dirname(__FILE__));
		$path .= DS . 'index.php';
		$exts = ilCore::getExtensions();
		$last = count($exts->data);
		$this->extension = $exts->data[$last-1];
		$this->_db = new ilInstallerDatabase(); // special DB class that includes more build methods
	}

	function check() {
		$sql = $this->_db->buildSelect(IL_TABLE_EXTENSIONS, array('name' => $this->extension->name));
		if ($this->_db->query($sql)) {
			$this->oldVersion = $this->_db->getResult();
		}
	}

	function importXmlFile ($file) {}

	function renameOldSchema () {} // nothing here by default

	function process ($file, $version=null) {
		$path = SCRIPT_ROOT . DS . 'extensions' . DS . $this->extension->name . DS . 'sql' . DS . $file . '.xml';
		if (file_exists($path)) {
			$schema = $this->importXmlFile($path);
			foreach ($schema as $query) {
				if (isset($version) && isset($query->version) && $version > $query->version) {
					continue; // skip queries that are for older versions of what's already installed
				}
				$sql = $this->_db->build($query);
				$this->_db->query($sql);
			}
		}
	}

	function createDB () {
		if (isset($this->oldVersion) ) {
			$this->renameOldSchema ();
		}
		$this->process('schema');
	}

	function upgrade () { // only called if oldVersion is not null
		$this->process('upgrade', $this->oldVersion);
	}

	function populateDB ($prefab=false) { // only called in oldVersion is null
		$this->process('data', $this->oldVersion);
		if ($prefab) {
			$this->process('extra_data', $this->oldVersion);
		}
	}

	function cleanDB () {
		$this->process('clean', $this->oldVersion);
	}
}

class ilInstaller extends ilParent {}

// additional steps will insert the extension, rebuild the panels, attempt to activate the plugin and resolve dependancies, and (if successful) rebuild the related caches (e.g. plugins, templates).