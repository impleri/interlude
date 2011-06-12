<?php
/**
 * system config object
 *
 * @package interlude
 * @subpackage app
 * @copyright Christopher Roussel <christopher@impleri.net>
 */

if (!defined('PLAY_MUSIC')) {
	die('Play it from the top, Sammie.');
}


class ilTableConfig extends ilParentTable {
	private $data;
	private $data_time;
	private $from_cache;
	private $dynamic;
	private $differ;
	private $recache;
	private $_root;

	/**
	 * Creates the config object and stores skeletal info
	 */
	function __construct() {
		$this->data = array();
		$this->data_time = 0;
		$this->from_cache = false;
		$this->recache = false;
		$this->_root = $root_path;
	}

	/**
	 * Loads config data from SQL and cache
	 */
	function read($force=false) {
		// get dynamic values
		$sql = "SELECT `sc_name`, `sc_value`
				FROM `%__config`
				WHERE `sc_static` = '0'";
		$db->setQuery($sql);
		if (!($rows = $db->loadAssocList('sc_name'))) {
			return false;
		}
		$this->data_time = time();
		foreach($rows as $row) {
			$this->data[$row['sc_name']] = $row['sc_value'];
		}

		// get static values
		$db_cached = new cache('dta_config', $this->data['cache_cfg']);
		$sql = "SELECT `sc_name`, `sc_value`
				FROM `%__config`
				WHERE `sc_static` = '1'";
		$rows = $db_cached->read($sql, $force, 'sc_name');
		if ( !empty($rows) ) {
			foreach($rows as $row) {
				$data[$row['sc_name']] = $row['sc_value'];
			}
			$this->data = array_merge($this->data, $data);
			unset($data);
			$this->data_time = $db_cached->data_time;
		}
		$this->from_cache = $db_cached->from_cache;

		return true;
	}
}
