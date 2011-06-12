<?php
/**
 * generic cache object
 *
 * @package interlude
 * @subpackage framework
 * @copyright Christopher Roussel <christopher@impleri.net>
 */

if (!defined('PLAY_MUSIC')) {
	die('Play it from the top, Sammie.');
}

class ilParentCache extends ilParentObject {
	var $_cache = null;

	function __construct() {
		$this->_cache = $this->get('cache');
	}

	function &getCache() {
		return ilCore::getCache();
	}
}

class ilDataCacheParent extends ilDataParent {
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

class ilCache extends ilParent {


}