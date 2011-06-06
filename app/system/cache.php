<?php
/**
 * Cache classes
 *
 * @author Christopher Roussel <christopher@impleri.net>
 * @version $Id$
 * @package Interlude
 * @filesource
 */

if (!defined('PLAY_MUSIC')) {
	die('Play it from the top, Sammie.');
}

class ilCacheParent extends ilParent {
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