<?php
/**
 * generic data cache object
 *
 * @package interlude
 * @subpackage framework
 * @copyright Christopher Roussel <christopher@impleri.net>
 */

if (!defined('PLAY_MUSIC')) {
	die('Play it from the top, Sammie.');
}

class ilParentDataCache extends ilParentCache {
	var $_cache = null;

	function __construct() {
		$this->_cache = $this->get('cache');
	}

	function &getCache() {
		return ilCore::getCache();
	}

	function loadTable() {}

	function reset() {}
}
