<?php
/**
 * generic object
 *
 * @package interlude
 * @subpackage framework
 * @copyright Christopher Roussel <christopher@impleri.net>
 */

if (!defined('PLAY_MUSIC')) {
	die('Play it from the top, Sammie.');
}

/**
 * generic object class
 *
 * simple object class inherited by all non-static classes
 */
class ilParentObject {
	/**
	 * @var array of errors
	 */
	protected $_errors = array();

	/**
	 * generic get method
	 *
	 * this first checks for a method named get{$Name} before looking for
	 * a property named $name.
	 *
	 * @param string property name
	 * @return mixed (default is null)
	 */
	public function get ($name) {
		// do not allow _private variables
		if ($name[0] === '_') {
			$this->setError('IL_OBJECT_PROHIBIT_ACCESS_TO_NONPUBLIC');
			return null;
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
	 * generic set method
	 *
	 * this first checks for a method named set{$Name} before looking for
	 * a property named $name.
	 *
	 * @param string property name
	 * @param mixed property value
	 */
	public function set ($name, $value) {
		// do not allow _private variables
		if ($name[0] === '_') {
			$this->setError('IL_OBJECT_PROHIBIT_ACCESS_TO_NONPUBLIC');
			return null;
		}

		$method = 'set' . ucfirst($name);
		if (method_exists($this, $method)) {
			$this->$method($value);
		}
		else {
			$this->$name = $value;
		}
	}

	/**
	 * check if an error is set
	 *
	 * @return boolean true on success
	 */
	public function hasError() {
		return (!empty($this->_errors);
	}

	/**
	 * get last error (if any)
	 *
	 * @return string|boolean string if exists, false if not
	 */
	public function getError() {
		$errors = array_reverse($this->_errors);
		return (empty($errors)) ? false : $errors[0];
	}

	/**
	 * get all errors
	 *
	 * @return array of errors
	 */
	public function getErrors() {
		return $this->_errors;
	}

	/**
	 * add an error to $this::$_errors after passing it through ilLanguage
	 *
	 * @param string error message
	 * @param string|array additional part to add to end of error message
	 */
	public function setError ($msg, $extra='') {
		$extra = (is_array($extra)) ? implode("\n", $extra) : $extra;
		$this->_errors[] = ilLanguage::_($msg) . $extra;
	}

	/**
	 * get object properties
	 *
	 * @param boolean
	 * @return array of properties
	 */
	public function getProperties ($public=true) {}
}
