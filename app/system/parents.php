<?php
/**
 * Basic parent classes
 *
 * @author Christopher Roussel <christopher@impleri.net>
 * @version $Id$
 * @package Interlude
 * @filesource
 */

if (!defined('PLAY_MUSIC')) {
	die('Play it from the top, Sammie.');
}

/**
 * Generic object class
 *
 * Main object class inherited by all non-static classes
 *
 * @package Interlude
 * @subpackage Core
 */
class ilParent {
	var $_errors = array();
	/**
	 * Generic get method
	 *
	 * @access public
	 * @param string $name property to get
	 * @return mixed
	 */
	function get ($name) {
		// do not allow _private variables
		if ($name[0] === '_') {
			return;
		}

		$ret = null;
		$method = 'get' . ucfirst($name);
		if (method_exists($this, $method)) {
			$ret = $this->$method();
		}
		elseif (isset($this->$name)) {
			$ret = $this->$name;
		}

		return $ret;
	}

	/**
	 * Generic set method
	 *
	 * @access public
	 * @param string $name property to set
	 * @param mixed $value
	 */
	function set ($name, $value) {
		// do not allow _private variables
		if ($name[0] === '_') {
			return;
		}

		$method = 'set' . ucfirst($name);
		if (method_exists($this, $method)) {
			$this->$method($value);
		}
		else {
			$this->$name = $value;
		}
	}

	function hasError() {}

	function getError () {}

	function getErrors() {
		return $this->_errors;
	}

	function setError ($msg) {}

	function getProperties() {}
}

/**
 * Generic database object class
 *
 * Main database object class inherited by all database table classes
 *
 * @package Interlude
 * @subpackage Core
 */
class ilDataParent extends ilParent {
	var $_db = null;
	var $_table = '';
	var $_key = '';

	function __construct() {} // sets private fields

	function load() {}

	function save() {}

	function delete() {}

	function reset() {}

	function validate() {}
}